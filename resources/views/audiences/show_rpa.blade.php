@extends('layout')
@section('content')
<h2>Audiencia: {{$audience->name}}</h2>
<h3>Campaña: {{$campaign->name}}</h4>
<?php 
function sendMessage($cel, $msg){
	$url = "https://api.whatsapp.com/send/?phone=";
	$url .= $cel;
	$url .= "/";
	$url .= "&text=".$msg;

	return $url;
}	

?>
	<table class="table">
		<tr>
			<td>GUARDAR ACCIÓN</td>
			@foreach($campaignMessage as $key => $value)
				<td>
					<?php echo "MENSAJE " . ($key + 1); ?>	
				</td>
			@endforeach
		</tr>
		@foreach($model as $item)
		@if($item->hasAValidPhone())
		<tr>
			<td>
				<?php $msg = 'Se envió campaña: '.$campaign->name?>
				<?php $str = 'https://mqe.quirky.com.co/track_send/'.$item->id.'/'.$audience->id.'/14/'.urlencode($msg); ?>
				<a target="iframe_a" href="{{$str}}">{{$str}}</a>	
			</td>
			@foreach($campaignMessage as $message)
			<td>
				<?php 
					$messageEncode = urlencode($message->text);
				?>
				<a target="whatsapp" href="{{ sendMessage($item->getPhone(), $messageEncode ) }}">{{ sendMessage($item->getPhone(),$messageEncode) }}</a>	
			</td>
			@endforeach
		</tr>
		@endif
		@endforeach
	</table>	
@endsection