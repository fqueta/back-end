<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ComponentController extends Controller
{
    /**
     * Lista componentes (post_type=componentes) com paginação opcional.
     * Suporta filtros via querystring:
     * - search: filtro por nome (LIKE em post_title)
     * - tipo_conteudo: aceita slug (guid) ou ID de tipo_conteudo
     * - id_curso: filtra por config.id_curso (valor numérico)
     * - ativo: 's' ou 'n' (normaliza para publish/draft)
     * - ordenar: filtra por menu_order (inteiro)
     * Inclui slug (post_name como slug), nome do tipo de conteúdo e
     * nome do curso (se configurado em config.id_curso).
     */
    public function index(Request $request)
    {
        $query = Post::query()->where('post_type', 'componentes')->where('deletado', '!=', 's');
        // Filtro: tipo_conteudo pode ser slug (guid) ou ID (resolve para post_name)
        if ($request->filled('tipo_conteudo')) {
            $tipo = (string) $request->input('tipo_conteudo');
            if (is_numeric($tipo)) {
                $tipoPost = Post::query()
                    ->where('post_type', 'tipo_conteudo')
                    ->find((int) $tipo);
                if ($tipoPost) {
                    $query->where('guid', $tipoPost->post_name);
                } else {
                    // Fallback: trata como slug caso não encontre por ID
                    $query->where('guid', $tipo);
                }
            } else {
                $query->where('guid', $tipo);
            }
        }
        // Filtro: id_curso armazenado em config.id_curso (JSON)
        if ($request->filled('id_curso')) {
            $idCurso = (int) $request->integer('id_curso');
            $query->where('config->id_curso', $idCurso);
        }
        // Filtro: ativo ('s'/'n') -> publish/draft
        if ($request->filled('ativo')) {
            $ativo = strtolower((string) $request->input('ativo'));
            if (in_array($ativo, ['s', 'n'])) {
                $query->where('post_status', $ativo === 's' ? 'publish' : 'draft');
            }
        }
        // Filtro: ordenar (menu_order)
        if ($request->filled('ordenar')) {
            $query->where('menu_order', $request->integer('ordenar'));
        }
        if ($request->filled('search')) {
            $query->where('post_title', 'like', '%' . $request->string('search') . '%');
        }
        $items = $query->orderBy('menu_order')->orderByDesc('ID')->paginate($request->integer('per_page', 15), [
            'ID as id',
            'post_title as nome',
            'post_status',
            'menu_order as ordenar',
            'post_name as short_code',
            'post_name as slug',
            'guid as tipo_conteudo',
            'config',
        ]);

        // Enriquecer cada item com nome do tipo de conteúdo e nome do curso
        $items->getCollection()->transform(function ($item) {
            // Normaliza post_status para ativo (s/n) e remove post_status do payload
            $item->ativo = ($item->post_status === 'publish') ? 's' : 'n';
            unset($item->post_status);

            // Nome do tipo de conteúdo: buscar por slug em guid
            if (!empty($item->tipo_conteudo)) {
                $ct = Post::query()
                    ->where('post_type', 'tipo_conteudo')
                    ->where('post_name', $item->tipo_conteudo)
                    ->first();
                $item->tipo_conteudo_nome = $ct?->post_title;
            } else {
                $item->tipo_conteudo_nome = null;
            }

            // Nome do curso, caso config.id_curso esteja definido
            $idCurso = is_array($item->config) ? ($item->config['id_curso'] ?? null) : null;
            if (!empty($idCurso)) {
                $curso = Curso::find($idCurso);
                $item->curso_nome = $curso?->nome;
            } else {
                $item->curso_nome = null;
            }

            return $item;
        });

        return response()->json($items);
    }

    /**
     * Obtém um componente pelo ID.
     */
    public function show(int $id)
    {
        $post = Post::where('post_type', 'componentes')->findOrFail($id);
        return response()->json([
            'id' => $post->ID,
            'nome' => $post->post_title,
            'tipo_conteudo' => $post->guid,
            'ordenar' => $post->menu_order,
            'short_code' => $post->post_name,
            'ativo' => $post->post_status === 'publish' ? 's' : 'n',
            'obs' => $post->post_content,
            'config' => $post->config,
        ]);
    }

    /**
     * Cria ou atualiza um componente baseado no payload informado.
     * Mapeamento:
     * - nome -> post_title
     * - tipo_conteudo -> guid (referência ao tipo)
     * - ordenar -> menu_order
     * - slug/short_code -> post_name (prioriza slug; gera se vazio)
     * - ativo (s/n) -> post_status (publish/draft)
     * - obs -> post_content (HTML)
     * - id_curso -> guardado em config
     * - token -> se vazio, gera automaticamente (mantém existente se já houver)
     * - autor -> preenchido automaticamente do usuário autenticado (UUID em config['autor_uuid'])
     */
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'id' => 'nullable|integer',
            'nome' => 'required|string|max:255',
            'tipo_conteudo' => 'required|string|max:50',
            'ordenar' => 'nullable|integer',
            'short_code' => 'nullable|string|max:200',
            'slug' => 'nullable|string|max:200',
            // Curso deve ser não obrigatório; aceita numérico
            'id_curso' => 'nullable|integer',
            'ativo' => 'nullable|in:s,n',
            'obs' => 'nullable|string',
            'token' => 'nullable|string|max:32',
            // autor é sempre obtido da requisição autenticada
        ])->validate();

        $post = null;
        if (!empty($validated['id'])) {
            $post = Post::where('post_type', 'componentes')->find($validated['id']);
        }
        if (!$post) {
            $post = new Post();
            $post->post_type = 'componentes';
        }

        $post->post_title = $validated['nome'];
        $post->guid = (string)$validated['tipo_conteudo'];
        $post->menu_order = (int)($validated['ordenar'] ?? 0);
        // post_name (slug): prioridade slug > short_code > gerar pelo nome se vazio
        if (!empty($validated['slug'])) {
            $post->post_name = Str::slug($validated['slug']);
        } elseif (!empty($validated['short_code'])) {
            $post->post_name = $validated['short_code'];
        } elseif (empty($post->post_name)) {
            $post->post_name = $post->generateSlug($validated['nome']);
        }
        $post->post_status = ($validated['ativo'] ?? 's') === 's' ? 'publish' : 'draft';
        $post->post_content = $validated['obs'] ?? '';

        // Config extras (somente id_curso)
        $config = $post->config ?? [];
        $config['id_curso'] = array_key_exists('id_curso', $validated)
            ? (int) $validated['id_curso']
            : ($config['id_curso'] ?? null);
        // O UUID de autor pode ser adicionado abaixo, se aplicável

        // Autor: sempre obtido do usuário autenticado
        $user = $request->user();
        if ($user && !empty($user->id)) {
            $post->post_author = $user->id; // UUID suportado pelo projeto
            $config['autor_uuid'] = $user->id; // guarda também no config
        } else {
            // sem usuário autenticado, mantém valor existente ou 0
            $post->post_author = $post->post_author ?? 0;
        }

        // Persistir config consolidada
        $post->config = $config;

        // Token: mantém existente ou gera automaticamente se vazio
        $incomingToken = $validated['token'] ?? null;
        if (!empty($incomingToken)) {
            $post->token = $incomingToken;
        } elseif (empty($post->token)) {
            $post->token = Str::random(16);
        }

        $post->save();

        // Resposta: retornar os dados gravados
        $responseData = [
            'id' => $post->ID,
            'nome' => $post->post_title,
            'slug' => $post->post_name,
            'tipo_conteudo' => $post->guid,
            'ordenar' => $post->menu_order,
            'ativo' => $post->post_status === 'publish' ? 's' : 'n',
            'obs' => $post->post_content,
            'id_curso' => ($post->config['id_curso'] ?? null),
            'token' => $post->token,
            'autor_uuid' => ($post->config['autor_uuid'] ?? null),
        ];

        return response()->json(['data' => $responseData], empty($validated['id']) ? 201 : 200);
    }

    /**
     * Atualiza um componente via rota REST (PUT/PATCH).
     * Encaminha para store() reaproveitando a validação e o mapeamento.
     */
    public function update(Request $request, int $id)
    {
        $request->merge(['id' => $id]);
        return $this->store($request);
    }

    /**
     * Remove logicamente (marca deletado) um componente.
     */
    public function destroy(int $id)
    {
        $post = Post::where('post_type', 'componentes')->findOrFail($id);
        $post->deletado = 's';
        $post->reg_deletado = now();
        $post->save();
        return response()->json(['ok' => true]);
    }
}