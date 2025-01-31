@extends('empty')@section('content')
<table class="" border="1" id="content">
	<tr>
		<td colspan="4"><h1>LA QUINTA- APTO 609 -VISTA CERROS DE CHIPRE /SEMITERMINADO</h1></td>
		<td colspan="3">
			<img src="https://trujillogutierrez.com.co/site/images/logo_trujillo_gutierrez_gris_rojo.png" alt="" class="d-inline" width="200">
			
		</td>
	</tr>
	<tr><td colspan="7" class="text-center subtitle"><h2>PRECIO Y FORMA DE PAGO</h2></td></tr>

	<tr>
		<td colspan="2">CARACTERISTICAS DEL INMUEBLE</td><td colspan="5">(  TIPO 1B)  3 habitaciones  + dos baños +estudio+ balcón</td>
	</tr>
	<tr>
		<td colspan="2">Valor de Apartamento</td><td>$ {{number_format($model->product->price,0)}}</td><td colspan="4">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">Parqueadero   	</td><td>$ @if(isset($model->parking)){{number_format($model->parking->price,0)}} @else 0 @endif</td><td colspan="4">  Sin parqueadero (    )     Cubierto (  x    )        Descubierto (     )			</td>
	</tr>
	<tr>
		<td colspan="2">Cuarto útil en sótano	</td><td>  $ - </td><td colspan="4"></td>
	</tr>
	<tr>
		<td colspan="2">Cuota inicial 30%	</td><td>  $ 60,752,606  </td><td colspan="4"></td>
	</tr>
	<tr>
		<td colspan="2">Separación	</td><td>  $ 3,000,000  </td><td colspan="4">Primera cuota (Hace parte de la cuota inicial)			</td>
	</tr>
	<tr>
		<td colspan="2">Cesantías 	</td><td>  $ - </td><td colspan="4">Presentar certificado a la fecha de cierre ( SI APLICA)			</td>
	</tr>
	<tr>
		<td colspan="2">Ahorro Programado	 	</td><td>  $ -  </td><td colspan="4">Presentar certificado a la fecha ( SI APLICA)			</td>
	</tr>
	<tr>
		<td colspan="2">Crédito Hipotecario 70%	 	</td><td>  $ 141,756,081 </td><td colspan="4">PRESENTARLO AL MOMENTO DE LA SEPARACION  DEL INMUEBLES (S)			</td>

	</tr>

	<tr><td colspan="7" class="text-center subtitle"><h2>DESCRIPCION DE LA UNIDAD</h2></td></tr>
	<tr><td>Apto Nº  609</td><td>Piso   6</td><td colspan="5">Area Construida 61,68m2   (incluido área   balcón de 3,14mts2)  /  Area Privada 51,51mts2				</td></tr>	
	<tr><td colspan="7">Obra Negra (   )      Semiterminado (  X  )     Full Acabados (     )						</td></tr>
	<tr><td colspan="7" class="text-center subtitle"><h2>CUOTA INICIAL</h2></td></tr>
	<tr><td colspan="6">Fecha estimada de entrega: 					</td><td>marzo -2022 aprox</td></tr>
	<tr>
		<td>Separado:</td><td> $ 3,000,000 </td>
		<td colspan="2">Numero de cuotas:	</td>
		<td>18</td>
		<td>Vr. Cuota:</td>
		<td> $ 3,208,478 </td>
	</tr>
	<tr>
		<td colspan="1">Fecha Cotización</td><td>18/11/2020</td><td colspan="5"></td>
	</tr>
	<tr>
		<td colspan="2">CREDITO HIPOTECARIO	</td>
		<td colspan="2">SIMULACION CREDITO BANCO / DATOS APROXIMADOS	</td>
		<td colspan="3">INGRESOS GRUPO FAMILIAR O UNIPERSONAL		</td>

	</tr>
	<tr>
		<td>Cuota Mes Aprox</td>
		<td> $ 1,115,053.3 </td>
		<td>30 años UVR</td>
		<td colspan="3">&nbsp;</td>
		<td>$ 3,716,844.4 </td>
	</tr>
	<tr>
		<td colspan="7">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="7">La lista de precios y forma de pago es vigente al 20 de agosto  de  2020						</td>
	</tr>
	<tr>
		<td colspan="7">Los precios estan sujetos a cambios y revisión por parte de la constructora, en todo caso se respetaran los precios para las ventas que se hayan separado con consignación.						</td>
	</tr>
	<tr><td colspan="7">&nbsp;</td></tr>

	<td colspan="7" class="text-center subtitle"><h2>CONTACTO ASESORA COMERCIAL</h2></td>

	<tr><td colspan="2">Nombre:	</td><td colspan="5">NATALIA A. MEJIA SOTO				</td></tr>
	<tr><td colspan="2">Celular:</td><td colspan="5">314 7084514  /  3113409251				</td></tr>
	<tr><td colspan="2">Email:</td><td colspan="5">saladeventas@trujillogutierrez.com.co / ventas@trujillogutierrez.com.co</td></tr>
	<tr><td colspan="7">Ventas Directas						</td></tr>
	<tr><td colspan="7">La Quinta Conjunto Cerrado : Cra 17 No. 4-33- La Francia						</td></tr>
	<tr><td colspan="7">Sede Administrativa. Edificio Fórum Cra 23 C No. 62-06  oficina 303 - Tel :  (6)  8930944						</td></tr>
	<tr><td colspan="7">Magnífica Vista, Ventilación Natural, Aire Fresco, Excelente Transporte						
						</td></tr>
	<tr><td colspan="7">www.trujillogutierrez.com.co</td></tr>
	<tr><td colspan="7">SIGUENOS EN </td></tr>
	<tr><td colspan="7">facebook.com/trujillogutierrezasociados						</td></tr>
	<tr><td colspan="7">instagram.com/trujillogutierrezsas/						</td></tr>
	

</table>



@endsection