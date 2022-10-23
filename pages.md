## auditoriaListaOperaciones

- model: operaciones
- toolbar
  - Agregar seleccionados a muestra
- multiSelect: true
- multiSelect1: true
- multiSelect2: true

| ID  | Cod Préstamo         | Asesor            | Producto             | Monto Desemb         | Saldo                | Moneda            | Fecha Desemb         | Ini Plan                 | Ult Pago            | Incumplimiento                | Cierre                | Nro Cuotas            | Plazo                | Mora            | Estado            | Tasa de interes | Form                 | Gasto            |
| --- | -------------------- | ----------------- | -------------------- | -------------------- | -------------------- | ----------------- | -------------------- | ------------------------ | ------------------- | ----------------------------- | --------------------- | --------------------- | -------------------- | --------------- | ----------------- | --------------- | -------------------- | ---------------- |
| id  | attributes.prmprnpre | attributes.asesor | attributes.prtcrdesc | attributes.prmprmdes | attributes.prmprsald | attributes.moneda | attributes.prmprfdes | attributes.ini_plan_pago | attributes.ult_pago | attributes.fec_incumplimiento | attributes.fec_cierre | attributes.num_cuotas | attributes.prmprplaz | attributes.mora | attributes.estado | attributes.tasa | attributes.prmprfpvc | attributes.gasto |
|     |                      |                   |                      | format: currency     | format: currency     |                   |                      |                          |                     |                               |                       |                       |                      |                 |                   | align: right    |                      | format: currency |
|     |                      |                   |                      | align: right         | align: right         |                   |                      |                          |                     |                               |                       |                       |                      |                 |                   |                 |                      | align: right     |
