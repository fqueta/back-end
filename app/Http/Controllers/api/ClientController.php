<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Requests\StoreClientesRequestApi;
use App\Http\Requests\UpdateClientesRequestApi;
use App\Models\User;
use App\Services\Qlib;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function index()
    {
        return response()->json(Client::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:clients',
            'phone' => 'nullable|string|max:50',
            'document' => 'nullable|string|max:20',
            'zip_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'active' => 'boolean',
        ], [
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto válido.',
            'name.max' => 'O nome não pode ter mais que 255 caracteres.',

            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.max' => 'O e-mail não pode ter mais que 255 caracteres.',
            'email.unique' => 'Este e-mail já está cadastrado.',

            'phone.string' => 'O telefone deve ser um texto válido.',
            'phone.max' => 'O telefone não pode ter mais que 50 caracteres.',

            'document.string' => 'O documento deve ser um texto válido.',
            'document.max' => 'O documento não pode ter mais que 20 caracteres.',

            'zip_code.string' => 'O CEP deve ser um texto válido.',
            'zip_code.max' => 'O CEP não pode ter mais que 10 caracteres.',

            'address.string' => 'O endereço deve ser um texto válido.',
            'address.max' => 'O endereço não pode ter mais que 255 caracteres.',

            'city.string' => 'A cidade deve ser um texto válido.',
            'city.max' => 'A cidade não pode ter mais que 100 caracteres.',

            'state.string' => 'O estado deve ser um texto válido.',
            'state.max' => 'O estado deve ter apenas 2 caracteres.',

            'active.boolean' => 'O campo ativo deve ser verdadeiro ou falso.',
        ]);
        $d = $request->all();
        // dd($data);
        $client = Client::create($data);

        return response()->json($client, 201);
    }
    // public function store(StoreClientesRequestApi $request)
    // {
    //     dd($request->all());
    //     $user_id = Auth::id();
    //     $ret = ['exec'=>false,'status'=>400,'response'=>'Erro ao salvar','data'=>[]];
    //     if($user_id){
    //         $d = $request->all();
    //         $ret['exec'] = false;
    //         $ret['status'] = 400;
    //         $ret['response'] = 'Erro ao salvar';
    //         $ret['data'] = $d;
    //         // $ret['user'] = $user;
    //         // return $ret;
    //         // $produtoParceiro = Qlib::qoption('produtoParceiro') ? Qlib::qoption('produtoParceiro') : 10232;

    //         $arr_campos = [
    //             'name'=>'nome',
    //             'cpf'=>'cpf',
    //             'genero'=>'sexo',
    //             // 'config'=>[
    //             //     'celular'=>'celular',
    //             //     'telefone_residencial'=>'telefone_residencial',
    //             //     'telefone_comercial'=>'telefone_comercial',
    //             //     'rg'=>'rg',
    //             //     'nascimento'=>'nascimento',
    //             //     'cep'=>'cep',
    //             //     'endereco'=>'endereco',
    //             //     'numero'=>'numero',
    //             //     'complemento'=>'complemento',
    //             //     'bairro'=>'bairro',
    //             //     'cidade'=>'cidade',
    //             //     'uf'=>'uf',
    //             //     'inicioVigencia'=>'inicio_vigencia',
    //             //     'fimVigencia'=>'fim_vigencia',
    //             // ],
    //         ];
    //         $ds=[];
    //         foreach ($arr_campos as $key => $val) {
    //             if(is_array($val)){
    //                 foreach ($val as $k1 => $va1) {
    //                     if(isset($d[$va1])){
    //                         $ds[$key][$k1] = $d[$va1];
    //                     }
    //                 }
    //             }else{
    //                 if(isset($d[$val])){
    //                     $ds[$key] = $d[$val];
    //                 }
    //             }
    //         }
    //         // $ds['config']['id_produto'] = isset($ds['id_produto']) ? $ds['id_produto'] : $produtoParceiro;
    //         $ds['config']['nome_fantasia'] = isset($ds['nome_fantasia']) ? $ds['nome_fantasia'] : null;
    //         $ds['ativo'] = isset($ds['ativo']) ? $ds['ativo'] : 's';

    //         // $ret['d'] = $ds;
    //         if(count($ds)>1){
    //             $inicioVigencia = isset($ds['config']['inicioVigencia']) ? $ds['config']['inicioVigencia'] : null;
    //             // dd($inicioVigencia);
    //             if($inicioVigencia){
    //                 $str_inicioVigencia = strtoupper($inicioVigencia);
    //                 $str_hoje = strtoupper(date('Y-m-d'));
    //                 // dd($str_inicioVigencia,$str_hoje,($str_inicioVigencia<$str_hoje));
    //                 if($str_inicioVigencia<$str_hoje){
    //                     $ret['response'] = 'Data de início de vigência inválida.\n';
    //                     return $ret;
    //                 }
    //             }
    //             $ds['autor'] = $user_id;
    //             $ds['id_permission'] = Qlib::qoption('id_permission_clientes');
    //             $ds['token'] = uniqid();
    //             $salv = (new ClienteController())->salvar_clientes($ds,true);
    //             if(isset($salv->original)){
    //                 $ret = $salv->original;
    //                 unset($ret['data']['valorPremio']);
    //                 $ret['status'] = 200;
    //             }
    //         }


    //     }else{

    //     }
    //     return response()->json($ret);
    // }
    public function show(Client $client)
    {
        return response()->json($client);
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:clients,email,' . $client->id,
            'phone' => 'nullable|string|max:50',
            'document' => 'nullable|string|max:20',
            'zip_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'active' => 'boolean',
        ]);

        $client->update($data);

        return response()->json($client);
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return response()->json(null, 204);
    }
}
