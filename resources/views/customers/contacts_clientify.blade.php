<!DOCTYPE html>
<html>

<head>
    <title>Contact List from Clientify</title>
</head>

<body>
    <h1>Contact List</h1>
    @if(isset($contacts))
    <ul>
        @foreach($contacts as $contact)
        <li>{{ $contact->first_name }} {{ $contact->last_name }}</li>
        @foreach ($contact->phones as $phone)
            <li>{{ $phone->phone }}</li>
        @endforeach
        @foreach ($contact->emails as $email)
            <li>{{ $email->email }}</li>
            @endforeach
        @endforeach
    </ul>
    @else
    <p>{{ $error }}</p>
    @endif
</body>

</html>