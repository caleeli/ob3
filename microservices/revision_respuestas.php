<?php

class RevisionRespuestas
{
    public $table = 'respuestas';
    public $join = 'right join preguntas on (respuestas.pregunta_number = preguntas.number and respuestas.tipo_credito = preguntas.tipo_credito and respuestas.informe=:informe_id and respuestas.prmprnpre=:prmprnpre_num and respuestas.calidad=:calidad)';
    public $id = 'respuestas.id';
    // enable do insert when ID is null or not provided during update
    public $insertOnUpdate = true;
    public $attributes = [
        'informe' => ':informe_id',
        'prmprnpre' => ':prmprnpre_num',
        'calidad' => ':calidad',

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
        'respuesta_jefe_agencia' => 'respuesta_jefe_agencia',
        'seguimiento' => 'seguimiento',
    ];
    public $where = [
        'and preguntas.tipo_credito=:tipo_credito',
    ];
    public $filters = [
    ];
    public $create = [
        'informe' => ':informe',
        'prmprnpre' => ':prmprnpre',
        'pregunta_number' => ':number',
        'tipo_credito' => ':tipo_credito',
        'calidad' => ':calidad',

        'revision' => ':revision',
        'observacion' => ':observacion',
        'tipo_observacion' => ':tipo_observacion',
        'riesgo_adicional' => ':riesgo_adicional',
    ];
    public $update = [
        'revision' => ':revision',
        'observacion' => ':observacion',
        'tipo_observacion' => ':tipo_observacion',
        'riesgo_adicional' => ':riesgo_adicional',
    ];
}

return RevisionRespuestas::class;
