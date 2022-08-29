<?php

class RevisionRespuestas
{
    public $table = 'preguntas';
    public $join = 'left join respuestas on (respuestas.pregunta_number = preguntas.number and respuestas.tipo_credito = preguntas.tipo_credito and respuestas.informe=:informe_id and respuestas.prmprnpre=:prmprnpre_num and respuestas.calidad=:calidad)';
    public $id = 'preguntas.id';
    public $attributes = [
        'number' => 'number',
        'tipo_credito' => 'preguntas.tipo_credito',
        'id_grupo' => 'id_grupo',
        'indice' => 'indice',
        'descripcion' => 'descripcion',
        'revision' => 'revision',
        'clasificacion' => 'clasificacion',
        'observacion' => 'observacion',
        'tipo_observacion' => 'tipo_observacion',
        'riesgo_adicional' => 'riesgo_adicional',
        'calidad' => 'calidad',
        'respuesta_jefe_agencia' => 'respuesta_jefe_agencia',
        'seguimiento' => 'seguimiento',
    ];
    public $where = [
        'and preguntas.tipo_credito=:tipo_credito',
    ];
    public $filters = [
    ];
    public $create = [
        'number' => ':number',
        'id_grupo' => ':id_grupo',
        'indice' => ':indice',
        'descripcion' => ':descripcion',
        'tipo_credito' => ':tipo_credito',
    ];
    public $update = [
        'number' => ':number',
        'id_grupo' => ':id_grupo',
        'indice' => ':indice',
        'descripcion' => ':descripcion',
        'tipo_credito' => ':tipo_credito',
    ];
}

return RevisionRespuestas::class;
