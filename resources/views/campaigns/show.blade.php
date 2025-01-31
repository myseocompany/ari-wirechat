@extends('layout')
@section('content')
<h2>Ver audiencia</h2>
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
			<td>GUARDAR ACCION</td>
			@foreach($messages as $key => $value)
				<td>
					<?php echo "MENSAJE " . ($key + 1); ?>	
				</td>
			@endforeach
		</tr>
		@foreach($model as $item)
		@if($item->hasAValidPhone())
		<tr>
			<td>
				<?php $str = 'Encuesta de satisfacción.';?>
				<?php $str = 'http://trujillo.quirky.com.co/track_send/'.$item->id.'/1/14/'.urlencode($str); ?>
				<a target="_blank" href="{{$str}}">Acción <?php echo $item->id ?></a>
			</td>
			@foreach($messages as $message)
			<td>
				<a target="_blank" href="{{ sendMessage($item->getPhone(), $message->name ) }}">{{$message->name}}</a>	
			</td>
			@endforeach
		</tr>
		@endif
		@endforeach
	</table>
@endsection