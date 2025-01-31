@extends('layoutExcel')

@section('content')
@if (count($model) > 0)
<?php  
function clearWP($str){
    $str = trim($str);
    $str = str_replace(" ", "", $str );
    $str = str_replace("+", "", $str );
    return $str;
} 

$output = "Celular;Nombre;Pais;Email;Comercial;Estado\n"; // Agregamos un salto de línea después de la cabecera

foreach($model as $key => $item) {
    $output .= (isset($item->phone) && $item->phone!="") ? clearWP($item->phone) : clearWP($item->phone2);
    $output .= ";" . html_entity_decode($item->name);
    $output .= ";" . $item->country;
    $output .= ";" . $item->email;
    $output .= ";" . (isset($item->user) ? $item->user->name : 'Sin Asesor');
    $output .= ";" . (isset($item->status) ? $item->status->name : '');
    $output .= "\n"; // Agregamos un salto de línea después de cada fila
}

// Eliminamos el último punto y coma y cualquier salto de línea adicional
echo rtrim($output, ";\n");

?>
@endif  
@endsection
