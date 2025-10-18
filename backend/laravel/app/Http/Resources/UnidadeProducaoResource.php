<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnidadeProducaoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome_cultura' => $this->nome_cultura,
            'cultura_label' => $this->crop_type_label,
            'area_total_ha' => $this->area_total_ha,
            'coordenadas_geograficas' => $this->coordenadas_geograficas,
            'propriedade_id' => $this->propriedade_id,
            'propriedade' => $this->whenLoaded('propriedade', function () {
                return [
                    'id' => $this->propriedade->id,
                    'nome' => $this->propriedade->nome,
                    'municipio' => $this->propriedade->municipio,
                    'uf' => $this->propriedade->uf,
                    'produtor' => $this->when($this->propriedade->relationLoaded('produtor'), function () {
                        return [
                            'id' => $this->propriedade->produtor->id,
                            'nome' => $this->propriedade->produtor->nome,
                        ];
                    }),
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
