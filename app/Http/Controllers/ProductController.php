<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $model = $this->filter($request);

       
        return view('products.index', compact('model'));
    }
    
    public function filter(Request $request){
        $model = Product::orderBy('id', 'ASC')->get();

        return $model;
    }

    public function edit($pid){
        $model = Product::find($pid);

        return view('products.edit', compact('model'));
    }

    public function update(Request $request, $pid){
        $model = Product::find($pid);
        if (!$model) {
            // Manejar el caso en que el producto no se encuentra
            return redirect()->back()->withErrors(['error' => 'Producto no encontrado.']);
        }

        // Actualizar los campos del modelo con los datos del formulario
        $model->name = $request->name;
        $model->alias = $request->alias;
        $model->active = $request->active;
        $model->price = $request->price;
        $model->coin = $request->coin;
        $model->country = $request->country;
        $model->total = $request->total;
        $model->shipping = $request->shipping;
        
        
        $model->colombia_price = $request->colombia_price;
        $model->america_price = $request->america_price;
        $model->america_shipping = $request->america_shipping;
        $model->ecuador_price = $request->ecuador_price;
        $model->ecuador_shipping = $request->ecuador_shipping;
        $model->ecuador_total_price = $request->ecuador_total_price;
        $model->europa_price = $request->europa_price;
        $model->europa_shipping = $request->europa_shipping;
        $model->colombia_coin = $request->colombia_coin;
        $model->europa_coin = $request->europa_coin;
        $model->america_coin = $request->america_coin;
        $model->registration = $request->registration;
        $model->category_id = $request->category_id;
        $model->description = $request->description;
        $model->description_to_print = $request->description_to_print;

        // Guardar los cambios en la base de datos
        $model->save();

        // Redirigir a una ruta deseada tras la actualización, por ejemplo:
        return redirect()->route('product-show', ['id' => $pid])->with('success', 'Producto actualizado correctamente.');
    }


    public function show($pid){
        $model = Product::find($pid);

        return view('products.show', compact('model'));

    }
    public function create(){
        

        return view('products.create');

    }

    public function store(Request $request){
        $model = new Product;
        

        // Actualizar los campos del modelo con los datos del formulario
        $model->name = $request->name;
        $model->alias = $request->alias;
        $model->active = $request->active;
        $model->price = $request->price;
        $model->coin = $request->coin;
        $model->total = $request->total;
        $model->country = $request->country;
        

        $model->shipping = $request->shipping;
        
        $model->colombia_price = $request->colombia_price;
        $model->america_price = $request->america_price;
        $model->america_shipping = $request->america_shipping;
        $model->ecuador_price = $request->ecuador_price;
        $model->ecuador_shipping = $request->ecuador_shipping;
        $model->ecuador_total_price = $request->ecuador_total_price;
        $model->europa_price = $request->europa_price;
        $model->europa_shipping = $request->europa_shipping;
        $model->colombia_coin = $request->colombia_coin;
        $model->europa_coin = $request->europa_coin;
        $model->america_coin = $request->america_coin;
        $model->registration = $request->registration;
        $model->category_id = $request->category_id;
        $model->description = $request->description;
        $model->description_to_print = $request->description_to_print;

        // Guardar los cambios en la base de datos
        $model->save();

        // Redirigir a una ruta deseada tras la actualización, por ejemplo:
        return redirect()->route('product-show', ['id' => $model->id])->with('success', 'Producto creado correctamente.');
    }

}
