<?php

class Muestra
{
    public $table = 'muestra';
    public $id = 'muestra.id';
    public $attributes = [
        'informe' => 'muestra.informe',
        'prmprnpre' => 'muestra.prmprnpre',
        'revisor' => 'muestra.revisor',
        'calidad' => 'muestra.calidad',
        'sucursal' => 'muestra.sucursal',
        'elaborado_por' => 'muestra.elaborado_por',
        'tipoinf' => 'muestra.tipoinf',
        'visita' => 'muestra.visita',
        'lugar_visita' => 'muestra.lugar_visita',
        'fecha_muestra' => 'muestra.fecha_muestra',
        'fecha_revision' => 'muestra.fecha_revision',
        'tipo_credito' => 'muestra.tipo_credito',
        'fecha_visita' => 'muestra.fecha_visita',
        'fecha_guardado' => 'muestra.fecha_guardado',
        'observaciones' => 'muestra.observaciones',
        'gbagenomb' => 'operaciones.gbagenomb',
        'prmprtcre' => 'operaciones.prmprtcre',
        'prtcrdesc' => 'operaciones.prtcrdesc',
        'prmprfdes' => 'operaciones.prmprfdes',
        'prmprmdes' => 'operaciones.prmprmdes',
        'prmprsald' => 'operaciones.prmprsald',
        'prmprcmon' => 'operaciones.prmprcmon',
        'moneda' => 'operaciones.moneda',
        'prmprstat' => 'operaciones.prmprstat',
        'estado' => 'operaciones.estado',
        'prmprplzo' => 'operaciones.prmprplzo',
        'prmprplaz' => 'operaciones.prmprplaz',
        'plaza' => 'operaciones.plaza',
        'prmpragen' => 'operaciones.prmpragen',
        'agencia' => 'operaciones.agencia',
        'prmprautp' => 'operaciones.prmprautp',
        'autoriza' => 'operaciones.autoriza',
        'prmprrseg' => 'operaciones.prmprrseg',
        'asesor' => 'operaciones.asesor',
        'ncreditos' => 'operaciones.ncreditos',
        'tasa' => 'operaciones.tasa',
        'gbagedir1' => 'operaciones.gbagedir1',
        'cargosdesem' => 'operaciones.cargosdesem',
        'cargosadmin' => 'operaciones.cargosadmin',
        'prmprfpvc' => 'operaciones.prmprfpvc',
        'mora' => 'operaciones.mora',
        'gbagecage' => 'operaciones.gbagecage',
        'ini_plan_pago' => 'operaciones.ini_plan_pago',
        'garantia' => 'operaciones.garantia',
        'gbciidesc' => 'operaciones.gbciidesc',
        'ult_pago' => 'operaciones.ult_pago',
        'prox_pago' => 'operaciones.prox_pago',
        'fec_incumplimiento' => 'operaciones.fec_incumplimiento',
        'fec_cierre' => 'operaciones.fec_cierre',
        'num_cuotas' => 'operaciones.num_cuotas',
        'tipo_plazo' => 'operaciones.tipo_plazo',
        'con_mora' => 'operaciones.con_mora',
        'gasto' => 'operaciones.gasto',
        'acbccccic' => 'operaciones.acbccccic',
    ];
    public $join = ' left join operaciones on (muestra.prmprnpre=operaciones.prmprnpre)';
    public $filters = [
    ];
    public $where = [
        'and tipo_credito <= 9',
    ];
}

return Muestra::class;
