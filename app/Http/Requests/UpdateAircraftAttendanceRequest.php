<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\AttendanceStatus;

/**
 * Request para atualização de atendimentos de aeronaves
 */
class UpdateAircraftAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Autorização será feita via middleware/policies
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Campos obrigatórios (podem ser opcionais na atualização)
            'aircraft_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:posts,ID'
            ],
            'service_order_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:service_orders,id'
            ],
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'min:3'
            ],
            'status' => [
                'sometimes',
                'required',
                Rule::in(array_column(AttendanceStatus::cases(), 'value'))
            ],
            'priority' => [
                'sometimes',
                'required',
                'in:low,medium,high,urgent'
            ],
            'started_at' => [
                'sometimes',
                'required',
                'date'
            ],
            'client_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:users,id'
            ],
            'client_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'min:2'
            ],
            
            // Campos opcionais
            'description' => [
                'nullable',
                'string',
                'max:5000'
            ],
            'current_funnel_id' => [
                'nullable',
                'integer'
            ],
            'current_funnel_name' => [
                'nullable',
                'string',
                'max:255'
            ],
            'current_stage_id' => [
                'nullable',
                'integer',
                'exists:stages,id'
            ],
            'current_stage_name' => [
                'nullable',
                'string',
                'max:255'
            ],
            'completed_at' => [
                'nullable',
                'date',
                'after_or_equal:started_at'
            ],
            'estimated_completion' => [
                'nullable',
                'date',
                'after_or_equal:started_at'
            ],
            'assigned_to' => [
                'nullable',
                'integer',
                'exists:users,id'
            ],
            'assigned_to_name' => [
                'nullable',
                'string',
                'max:255'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000'
            ],
            'internal_notes' => [
                'nullable',
                'string',
                'max:2000'
            ],
            'service_summary' => [
                'nullable',
                'array'
            ],
            'service_summary.*' => [
                'string'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'aircraft_id.required' => 'A aeronave é obrigatória.',
            'aircraft_id.exists' => 'A aeronave selecionada não existe.',
            'service_order_id.required' => 'A ordem de serviço é obrigatória.',
            'service_order_id.exists' => 'A ordem de serviço selecionada não existe.',
            'title.required' => 'O título é obrigatório.',
            'title.min' => 'O título deve ter pelo menos 3 caracteres.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status selecionado é inválido.',
            'priority.required' => 'A prioridade é obrigatória.',
            'priority.in' => 'A prioridade selecionada é inválida.',
            'started_at.required' => 'A data de início é obrigatória.',
            'started_at.date' => 'A data de início deve ser uma data válida.',
            'client_id.required' => 'O cliente é obrigatório.',
            'client_id.exists' => 'O cliente selecionado não existe.',
            'client_name.required' => 'O nome do cliente é obrigatório.',
            'client_name.min' => 'O nome do cliente deve ter pelo menos 2 caracteres.',
            'completed_at.after_or_equal' => 'A data de conclusão deve ser posterior à data de início.',
            'estimated_completion.after_or_equal' => 'A previsão de conclusão deve ser posterior à data de início.',
            'assigned_to.exists' => 'O responsável selecionado não existe.',
            'current_stage_id.exists' => 'A etapa selecionada não existe.',
            'description.max' => 'A descrição não pode ter mais de 5000 caracteres.',
            'notes.max' => 'As observações não podem ter mais de 2000 caracteres.',
            'internal_notes.max' => 'As observações internas não podem ter mais de 2000 caracteres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'aircraft_id' => 'aeronave',
            'service_order_id' => 'ordem de serviço',
            'title' => 'título',
            'status' => 'status',
            'priority' => 'prioridade',
            'started_at' => 'data de início',
            'client_id' => 'cliente',
            'client_name' => 'nome do cliente',
            'description' => 'descrição',
            'completed_at' => 'data de conclusão',
            'estimated_completion' => 'previsão de conclusão',
            'assigned_to' => 'responsável',
            'notes' => 'observações',
            'internal_notes' => 'observações internas',
        ];
    }
}
