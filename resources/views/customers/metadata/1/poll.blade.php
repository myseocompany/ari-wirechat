	@extends('layout')

	@section('content')


	<center>
			<br>
		<h2>Encuesta de satisfacción</h2>
	</center>
	<br>

	Queremos conocer qué tan satisfecho se encuentra con nuestros productos y servicios, para de esta manera ajustarnos más a sus necesidades. Para ello, solicitamos su colaboración con las siguientes preguntas, que nos permitirán conocer su percepción y satisfacción. Tomará solo 5 minutos.
	Gracias por ayudarnos a seguir creciendo!

	<br><br><br>

	<strong>Confirme estos datos por favor:</strong> <br>
	<form method="POST" action="/metadata/{{$customers->id}}/save">	 
		{{ csrf_field() }}
		<div class="table table-striped">
			<table>
				<thead>
					<tr>	
						<th>Nombre</th>
						<th>Correo</th>
						<th>Empresa</th>
						<th>Cargo</th>
						<th>Número de Empleados</th>

					</tr>
				</thead>
				<tbody>
					
					<tr>
						<td><input class="form-control" type="text" name="name" required="required" value="{{$customers->name}}" disabled=""></td>
						<td><input class="form-control" type="text" name="email" required="required" value="{{$customers->email}}"></td>
						<td><input class="form-control" type="text" name="business" required="required" value="{{$customers->business}}"></td>
						<td><input class="form-control" type="text" name="position" required="required" value="{{$customers->position}}"></td>
						<td><input class="form-control" type="number" name="number_employees" required="required" value=""></td>
					</tr>
					
				</tbody>
			</table>
		</div>
<br><br>

<div class="table table-striped">
	
		<strong>1. ¿Cuál es la producción diaria de empanadas en su empresa?</strong> 
		<table class="table">
			<tr><td><input type="radio" name="empanadas" value ="89" required=""> De 1 a 500 empanadas.</td></tr>
			<tr><td><input type="radio" name="empanadas" value ="90" required=""> De 501 a 2000 empanadas.</td></tr>	
			<tr><td><input type="radio" name="empanadas" value ="91" required=""> De 2001 a 5000 empanadas.</td></tr>
			<tr><td><input type="radio" name="empanadas" value ="92" required=""> De 5001 a 10.000 empanadas.</td></tr>
			<tr><td><input type="radio" name="empanadas" value ="93" required=""> De 10.000 o más empanadas.</td></tr>
		</table>	
		<br>
		<strong>2. Con respecto a la calidad de los productos califique de 1-5 su satisfacción:</strong>
		<br>
		<table class="table">
			<tr>
				<td>Insatisfecho</td><td> 1</td><td> 2</td><td> 3</td><td> 4</td><td> 5</td> <td>Satisfecho</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="radio" name="quality_78" value ="1" required=""> </td>
				<td><input type="radio" name="quality_78" value ="2" required=""> </td>
				<td><input type="radio" name="quality_78" value ="3" required=""> </td>
				<td><input type="radio" name="quality_78" value ="4" required=""> </td>
				<td><input type="radio" name="quality_78" value ="5" required=""> </td>
				<td></td>
			</tr>
		</table>
		<br><br>
		<strong>3. Con respecto a la comodidad (facilidad) en el uso de los productos califique de 1-5 su satisfacción:</strong>
		<br>
		<table class="table">
			<tr>
				<td>Insatisfecho</td><td> 1</td><td> 2</td><td> 3</td><td> 4</td><td> 5</td> <td>Satisfecho</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="radio" name="confort_79" value ="1" required=""> </td>
				<td><input type="radio" name="confort_79" value ="2" required=""> </td>
				<td><input type="radio" name="confort_79" value ="3" required=""> </td>
				<td><input type="radio" name="confort_79" value ="4" required=""> </td>
				<td><input type="radio" name="confort_79" value ="5" required=""> </td>
				<td></td>
			</tr>
		</table>
		<br><br>
		<strong>4. Con respecto a la seguridad en el uso de los productos califique de 1-5 su satisfacción:</strong>
		<br>
		<table class="table">
			<tr>
				<td>Insatisfecho</td><td> 1</td><td> 2</td><td> 3</td><td> 4</td><td> 5</td> <td>Satisfecho</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="radio" name="security_80" value ="1" required=""> </td>
				<td><input type="radio" name="security_80" value ="2" required=""> </td>
				<td><input type="radio" name="security_80" value ="3" required=""> </td>
				<td><input type="radio" name="security_80" value ="4" required=""> </td>
				<td><input type="radio" name="security_80" value ="5" > </td>
				<td></td>
			</tr>
		</table>
		<br><br>
		<strong>5. Con respecto al tiempo de entrega de los productos califique de 1-5 su satisfacción:</strong>
		<br>
		<table class="table">
			<tr>
				<td>Insatisfecho</td><td> 1</td><td> 2</td><td> 3</td><td> 4</td><td> 5</td> <td>Satisfecho</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="radio" name="delivery_time_81" value ="1" required=""> </td>
				<td><input type="radio" name="delivery_time_81" value ="2" required=""> </td>
				<td><input type="radio" name="delivery_time_81" value ="3" required=""> </td>
				<td><input type="radio" name="delivery_time_81" value ="4" required=""> </td>
				<td><input type="radio" name="delivery_time_81" value ="5" required=""> </td>
				<td></td>
			</tr>
		</table>
		<br><br>
		<strong>6. Con respecto a nuestro servicio atención y asesoría del personal de ventas califique de 1-5 su satisfacción:</strong>
		<br>
		<table class="table">
			<tr>
				<td>Insatisfecho</td><td> 1</td><td> 2</td><td> 3</td><td> 4</td><td> 5</td> <td>Satisfecho</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="radio" name="atention_82" value ="1" required=""> </td>
				<td><input type="radio" name="atention_82" value ="2" required=""> </td>
				<td><input type="radio" name="atention_82" value ="3" required=""> </td>
				<td><input type="radio" name="atention_82" value ="4" required=""> </td>
				<td><input type="radio" name="atention_82" value ="5" required=""> </td>
				<td></td>
			</tr>
		</table>
		<br><br>
	<strong>7. Con respecto a nuestro servicio tiempo de respuesta del personal de ventas califique de 1-5 su satisfacción:</strong>
		<br>
		<table class="table">
			<tr>
				<td>Insatisfecho</td><td> 1</td><td> 2</td><td> 3</td><td> 4</td><td> 5</td> <td>Satisfecho</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="radio" name="responsive_time_personal_83" value ="1" required=""> </td>
				<td><input type="radio" name="responsive_time_personal_83" value ="2" required=""> </td>
				<td><input type="radio" name="responsive_time_personal_83" value ="3" required=""> </td>
				<td><input type="radio" name="responsive_time_personal_83" value ="4" required=""> </td>
				<td><input type="radio" name="responsive_time_personal_83" value ="5" required=""> </td>
				<td></td>
			</tr>
		</table>
		<br><br>
		<strong>8. Con respecto a nuestro servicio tención y asesoría del personal de soporte técnico califique de 1-5 su satisfacción:</strong>
		<br>
		<table class="table">
			<tr>
				<td>Insatisfecho</td><td> 1</td><td> 2</td><td> 3</td><td> 4</td><td> 5</td> <td>Satisfecho</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="radio" name="atention_technical_support_84" value ="1" required=""> </td>
				<td><input type="radio" name="atention_technical_support_84" value ="2" required=""> </td>
				<td><input type="radio" name="atention_technical_support_84" value ="3" required=""> </td>
				<td><input type="radio" name="atention_technical_support_84" value ="4" required=""> </td>
				<td><input type="radio" name="atention_technical_support_84" value ="5" required=""> </td>
				<td></td>
			</tr>
		</table>
		<br><br>
		<strong>9. Con respecto a nuestro servicio Calidad del servicio de soporte técnico califique de 1-5 su satisfacción:</strong>
		<br>
		<table class="table">
			<tr>
				<td>Insatisfecho</td><td> 1</td><td> 2</td><td> 3</td><td> 4</td><td> 5</td> <td>Satisfecho</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="radio" name="quality_technical_support_85" value ="1" required=""> </td>
				<td><input type="radio" name="quality_technical_support_85" value ="2" required=""> </td>
				<td><input type="radio" name="quality_technical_support_85" value ="3" required=""> </td>
				<td><input type="radio" name="quality_technical_support_85" value ="4" required=""> </td>
				<td><input type="radio" name="quality_technical_support_85" value ="5" required=""> </td>
				<td></td>
			</tr>
		</table>
		<br><br>
		<strong>10. Con respecto a nuestro servicio Nivel de satisfacción general con la empresa califique de 1-5 su satisfacción:</strong>
		<br>
		<table class="table">
			<tr>
				<td>Insatisfecho</td><td> 1</td><td> 2</td><td> 3</td><td> 4</td><td> 5</td> <td>Satisfecho</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="radio" name="satisfaction_level_86" value ="1" required=""> </td>
				<td><input type="radio" name="satisfaction_level_86" value ="2" required=""> </td>
				<td><input type="radio" name="satisfaction_level_86" value ="3" required=""> </td>
				<td><input type="radio" name="satisfaction_level_86" value ="4" required=""> </td>
				<td><input type="radio" name="satisfaction_level_86" value ="5" required=""> </td>
				<td></td>
			</tr>
		</table>
		<br><br>

		<strong>11. Considerando su experiencia con nuestros productos y servicios ¿Cuál es la probabilidad de que recomiende a Maquiempanadas?.
		</strong>

		<br>
				<table class="table">
				  <tr>
					<td ></td>
					<td>Nada probable</td>
					<td>0</td>
					<td>1</td>
					<td>2</td>
					<td>3</td>
					<td>4</td>
					<td>5</td>
					<td>6</td>
					<td>7</td>
					<td>8</td>
					<td>9</td>
					<td>10</td>
					<td>Muy probable</td>
		



				  <tr>
					<td></td>
					<td></td>
					<td><input type="radio" name ="recommendation_87" value = "0" required=""></td>
					<td><input type="radio" name ="recommendation_87" value = "1" required=""></td>
					<td><input type="radio" name ="recommendation_87" value = "2" required=""></td>
					<td><input type="radio" name ="recommendation_87" value = "3" required=""></td>
					<td><input type="radio" name ="recommendation_87" value = "4" required=""></td>
					<td><input type="radio" name ="recommendation_87" value = "5" required=""></td>
					<td><input type="radio" name ="recommendation_87" value = "6" required=""></td>
					<td><input type="radio" name ="recommendation_87" value = "7" required=""></td>
					<td><input type="radio" name ="recommendation_87" value = "8" required=""></td>
					<td><input type="radio" name ="recommendation_87" value = "9" required=""></td>
					<td><input type="radio" name ="recommendation_87" value = "10" required=""></td>
					<td></td>

				  </tr>
				</table>
				<br>
		<strong>12. ¿Qué sugerencia(s) le haría a la empresa Maquiempanadas, para que esta mejore la experiencia y los productos entregados al cliente?</strong><br>
		 <textarea id="msg" name="suggestions_88" class="textinput" value=""></textarea>
			<br><br>
			<br>
			<br>
			<br>
<b>
	Sus datos personales han sido y están siendo tratados conforme con nuestra Política de Tratamiento de Datos Personales.Para mayor información podrá consultar nuestra política en la página web:
</b>
<a target="_blanck" href="/metadata/1/datapolicy">Política de Privacidad</a>
<br><br>
		<input type="checkbox" value="yes" name="data_authorization"> ¿Autoriza el envío de datos para publicidad?
		<br><br><br>
		<center>
			<input type="submit" class="btn btn-primary" value="Guardar">
		</center>
<style>
		.container {
			max-width: 820px;
			margin: 0px auto;
			margin-top: 50px;
		}

		.comment {
			float: left;
			width: 100%;
			height: auto;
		}

		.commenter {
			float: left;
		}

		.commenter img {
			width: 35px;
			height: 35px;
		}

		.comment-text-area {
			float: left;
			width: 100%;
			height: auto;
		}

		.textinput {
			float: left;
			width: 100%;
			min-height: 75px;
			outline: none;
			resize: none;
			border: 1px solid grey;
		}
	.table {
			float: left;
			width: 100%;
			min-height: 75px;
			outline: none;
			resize: none;
			
		}


	</style>
</form>
</div>
@endsection







