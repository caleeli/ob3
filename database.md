## operaciones

| Field              | Type          | Null | Key | Default | Extra          |
| ------------------ | ------------- | ---- | --- | ------- | -------------- |
| id                 | int(11)       | NO   | UNI |         | auto_increment |
| prmprnpre          | varchar(16)   | NO   | PRI |         |                |
| gbagenomb          | varchar(128)  | NO   |     |         |                |
| prmprtcre          | varchar(128)  | NO   |     |         |                |
| prtcrdesc          | varchar(128)  | NO   |     |         |                |
| prmprfdes          | varchar(128)  | NO   |     |         |                |
| prmprmdes          | varchar(128)  | NO   |     |         |                |
| prmprsald          | varchar(128)  | NO   |     |         |                |
| prmprcmon          | varchar(128)  | NO   |     |         |                |
| moneda             | varchar(128)  | NO   |     |         |                |
| prmprstat          | varchar(128)  | NO   |     |         |                |
| estado             | varchar(128)  | NO   |     |         |                |
| prmprplzo          | varchar(128)  | NO   |     |         |                |
| prmprplaz          | varchar(128)  | NO   |     |         |                |
| plaza              | varchar(128)  | NO   |     |         |                |
| prmpragen          | varchar(128)  | NO   |     |         |                |
| agencia            | varchar(128)  | NO   |     |         |                |
| prmprautp          | varchar(128)  | NO   |     |         |                |
| autoriza           | varchar(128)  | NO   |     |         |                |
| prmprrseg          | varchar(128)  | NO   |     |         |                |
| asesor             | varchar(128)  | NO   |     |         |                |
| ncreditos          | int(11)       | YES  |     |         |                |
| tasa               | varchar(11)   | YES  |     |         |                |
| gbagedir1          | varchar(200)  | YES  |     |         |                |
| cargosdesem        | varchar(2048) | YES  |     |         |                |
| cargosadmin        | varchar(2048) | YES  |     |         |                |
| prmprfpvc          | varchar(50)   | YES  |     |         |                |
| mora               | int(11)       | YES  |     |         |                |
| gbagecage          | int(11)       | YES  |     |         |                |
| ini_plan_pago      | varchar(32)   | YES  |     |         |                |
| garantia           | varchar(1024) | YES  |     |         |                |
| gbciidesc          | varchar(1024) | YES  |     |         |                |
| ult_pago           | varchar(32)   | YES  |     |         |                |
| prox_pago          | varchar(32)   | YES  |     |         |                |
| fec_incumplimiento | varchar(32)   | YES  |     |         |                |
| fec_cierre         | varchar(32)   | YES  |     |         |                |
| num_cuotas         | int(11)       | YES  |     |         |                |
| tipo_plazo         | varchar(64)   | YES  |     |         |                |
| con_mora           | varchar(11)   | YES  |     |         |                |
| gasto              | varchar(128)  | YES  |     |         |                |
| acbccccic          | int(11)       | YES  |     |         |                |
## muestra

| Field          | Type          | Null | Key | Default    | Extra          |
| -------------- | ------------- | ---- | --- | ---------- | -------------- |
| informe        | int(11)       | NO   | PRI |            | auto_increment |
| prmprnpre      | varchar(16)   | NO   | PRI |            |                |
| revisor        | int(11)       | NO   |     |            |                |
| calidad        | varchar(32)   | NO   | PRI | PRELIMINAR |                |
| sucursal       | varchar(128)  | YES  |     |            |                |
| elaborado_por  | int(11)       | YES  |     |            |                |
| tipoinf        | varchar(4)    | YES  |     |            |                |
| visita         | varchar(32)   | YES  |     |            |                |
| lugar_visita   | varchar(64)   | YES  |     |            |                |
| fecha_muestra  | varchar(32)   | YES  |     |            |                |
| fecha_revision | varchar(32)   | YES  |     |            |                |
| tipo_credito   | int(11)       | YES  |     | 1          |                |
| fecha_visita   | varchar(32)   | YES  |     |            |                |
| fecha_guardado | datetime      | NO   |     |            |                |
| observaciones  | varchar(2048) | YES  |     |            |                |
