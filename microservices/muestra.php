<?php

class Muestra
{
    public $table = 'muestra';
    public $id = 'muestra.id';
    public $attributes = [
        'informe' => 'informe',
        'prmprnpre' => 'muestra.prmprnpre',
        'revisor' => 'revisor',
        'calidad' => 'calidad',
        'sucursal' => 'sucursal',
        'elaborado_por' => 'elaborado_por',
        'tipoinf' => 'tipoinf',
        'visita' => 'visita',
        'lugar_visita' => 'lugar_visita',
        'fecha_muestra' => 'fecha_muestra',
        'fecha_revision' => 'fecha_revision',
        'tipo_credito' => 'tipo_credito',
        'fecha_visita' => 'fecha_visita',
        'fecha_guardado' => 'fecha_guardado',
        'observaciones' => 'observaciones',
        'prmprtcre' => 'operaciones.prmprtcre',
        'prtcrdesc' => 'operaciones.prtcrdesc',
        'prmprfdes' => 'operaciones.prmprfdes',
        'ini_plan_pago' => 'ini_plan_pago',
        'prmprmdes' => 'operaciones.prmprmdes',
        'prmprsald' => 'operaciones.prmprsald',
        'prmprcmon' => 'operaciones.prmprcmon',
        'moneda' => 'moneda',
        'prmprstat' => 'operaciones.prmprstat',
        'estado' => 'estado',
        'prmprplzo' => 'operaciones.prmprplzo',
        'prmprplaz' => 'operaciones.prmprplaz',
        'plaza' => 'plaza',
        'prmpragen' => 'operaciones.prmpragen',
        'agencia' => 'agencia',
        'prmprautp' => 'operaciones.prmprautp',
        'autoriza' => 'autoriza',
        'prmprrseg' => 'operaciones.prmprrseg',
        'asesor' => 'asesor',
    ];
    public $join = ' left join operaciones on (muestra.prmprnpre=operaciones.prmprnpre)';
    public $filters = [
    ];
    public $where = [
    ];
}

return Muestra::class;
