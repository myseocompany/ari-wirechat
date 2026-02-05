<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\CustomerStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Namu\WireChat\Models\Conversation;
use Namu\WireChat\Models\Message;
use Namu\WireChat\Models\Scopes\WithoutRemovedMessages;

class CustomerAssignedConversations extends Component
{
    public string $search = '';

    public $conversations;

    public bool $canLoadMore = false;

    public int $page = 1;

    public bool $adminOnlyMyAssigned = false;

    public ?int $selectedConversationId = null;

    public ?Conversation $selectedConversation = null;

    public Collection $selectedMessages;

    public ?int $customerStatusId = null;

    public Collection $customerStatuses;

    private int $perPage = 20;

    public function mount(): void
    {
        abort_unless(auth()->check(), 401);
        $this->conversations = collect();
        $this->selectedMessages = collect();
        $this->customerStatuses = CustomerStatus::query()
            ->orderBy('weight')
            ->get(['id', 'name']);
    }

    public function updatedSearch(): void
    {
        $this->reset(['page', 'canLoadMore']);
    }

    public function updatedAdminOnlyMyAssigned(): void
    {
        $this->reset(['page', 'canLoadMore', 'selectedConversationId']);
    }

    public function updatedCustomerStatusId(): void
    {
        $this->reset(['page', 'canLoadMore', 'selectedConversationId']);
    }

    public function loadMore(): void
    {
        if (! $this->canLoadMore) {
            return;
        }

        $this->page++;
    }

    protected function loadConversations(): void
    {
        $limit = $this->page * $this->perPage;

        $conversations = $this->accessibleConversationsQuery()
            ->latest('updated_at')
            ->take($limit + 1)
            ->get();

        $this->canLoadMore = $conversations->count() > $limit;
        $this->conversations = $conversations->take($limit)->values();
        $this->loadCustomerAdvisorRelations($this->conversations);

        if ($this->selectedConversationId === null && $this->conversations->isNotEmpty()) {
            $this->selectedConversationId = (int) $this->conversations->first()->id;
        }

        $this->loadSelectedConversation();
    }

    public function selectConversation(int $conversationId): void
    {
        if (! $this->accessibleConversationsQuery()->whereKey($conversationId)->exists()) {
            return;
        }

        $this->selectedConversationId = $conversationId;
        $this->loadSelectedConversation();
    }

    protected function loadSelectedConversation(): void
    {
        if ($this->selectedConversationId === null) {
            $this->selectedConversation = null;
            $this->selectedMessages = collect();

            return;
        }

        $conversation = $this->accessibleConversationsQuery()
            ->whereKey($this->selectedConversationId)
            ->first();

        if (! $conversation) {
            $this->selectedConversation = null;
            $this->selectedMessages = collect();

            return;
        }

        $this->selectedConversation = $conversation;
        $this->selectedMessages = Message::query()
            ->withoutGlobalScope(WithoutRemovedMessages::class)
            ->where('conversation_id', $conversation->id)
            ->with(['sendable', 'attachment'])
            ->oldest('created_at')
            ->take(80)
            ->get();

        $this->loadCustomerAdvisorRelations(collect([$conversation]));
    }

    protected function loadCustomerAdvisorRelations(Collection $conversations): void
    {
        $participants = new EloquentCollection($conversations->pluck('participants')->flatten()->all());
        $participants->loadMorph('participantable', [
            Customer::class => ['user'],
        ]);
    }

    protected function accessibleConversationsQuery(): Builder
    {
        $authUser = auth()->user();
        $authId = (int) $authUser->id;
        $customerMorph = (new Customer)->getMorphClass();
        $shouldLimitToAssignedCustomers = (int) $authUser->role_id !== 1 || $this->adminOnlyMyAssigned;

        return Conversation::query()
            ->with([
                'participants.participantable',
                'group.cover',
                'lastMessage' => function ($query): void {
                    $query->withoutGlobalScope(WithoutRemovedMessages::class)
                        ->with('attachment');
                },
            ])
            ->whereHas('messages', function (Builder $query): void {
                $query->withoutGlobalScope(WithoutRemovedMessages::class);
            })
            ->whereHas('participants', function (Builder $query) use ($authId, $customerMorph, $shouldLimitToAssignedCustomers): void {
                $query
                    ->where('participantable_type', $customerMorph)
                    ->when(trim($this->search) !== '', function (Builder $customerQuery): void {
                        $search = '%'.trim($this->search).'%';
                        $customerQuery->whereHasMorph('participantable', [Customer::class], function (Builder $participantableQuery) use ($search): void {
                            $participantableQuery->where(function (Builder $query) use ($search): void {
                                $query
                                    ->where('phone', 'like', $search)
                                    ->orWhere('phone2', 'like', $search);
                            });
                        });
                    })
                    ->when($this->customerStatusId, function (Builder $customerQuery): void {
                        $customerQuery->whereHasMorph('participantable', [Customer::class], function (Builder $participantableQuery): void {
                            $participantableQuery->where('status_id', $this->customerStatusId);
                        });
                    })
                    ->when($shouldLimitToAssignedCustomers, function (Builder $customerQuery) use ($authId): void {
                        $customerQuery->whereIn('participantable_id', $this->assignedCustomerIdsQuery($authId));
                    });
            });
    }

    protected function assignedCustomerIdsQuery(int $authId): Builder
    {
        return Customer::query()
            ->select('id')
            ->where('user_id', $authId);
    }

    #[Title('Conversaciones de mis clientes')]
    public function render(): View
    {
        $this->loadConversations();

        return view('livewire.customer-assigned-conversations')
            ->layout(config('wirechat.layout', 'wirechat::layouts.app'));
    }
}
