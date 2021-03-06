DROP TABLE IF EXISTS `banks`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `jobs_data`;

CREATE TABLE `banks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `alias` varchar(32) DEFAULT NULL,
  `code` varchar(5) DEFAULT NULL,
  `link` varchar(256) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `jobs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bank_id` char(2) DEFAULT NULL,
  `password` varchar(56) DEFAULT NULL,
  `user` varchar(128) DEFAULT NULL,
  `document_num` varchar(16) DEFAULT NULL,
  `document_type` ENUM('DNI', 'people') DEFAULT NULL,
  `hash` char(64) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `retries` tinyint(1) NOT NULL DEFAULT '0',
  `timestamp` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `jobs_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hash` char(64) NOT NULL,
  `data` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `banks` (`code`, `name`, `link`, `alias`)
VALUES
	('00007','BANCO DE GALICIA Y BUENOS AIRES S.A.U.','https://onlinebanking.bancogalicia.com.ar/login','galicia'),
	('00011','BANCO DE LA NACION ARGENTINA','https://hb.redlink.com.ar/bna/login.htm','nacion'),
	('00014','BANCO DE LA PROVINCIA DE BUENOS AIRES','https://www.bancoprovincia.bancainternet.com.ar/eBanking/login/inicio.htm','provincia'),
	('00015','INDUSTRIAL AND COMMERCIAL BANK OF CHINA','https://www.accessbanking.com.ar/RetailHomeBankingWeb/init.do','icbc'),
	('00016','CITIBANK N.A.','https://www.privatebank.citibank.com/','citibank'),
	('00017','BBVA BANCO FRANCES S.A.','https://www.bbva.com.ar/personas/servicios/canales-autogestion/banca-online/','bbva'),
  ('00020','BANCO DE LA PROVINCIA DE CORDOBA S.A.','https://bancon.bancor.com.ar/','bancor'),
	('00027','BANCO SUPERVIELLE S.A.','https://personas.supervielle.com.ar/LoginNuevo.aspx','supervielle'),
	('00029','BANCO DE LA CIUDAD DE BUENOS AIRES','https://hb.redlink.com.ar/ciudad/login.htm','ciudad'),
	('00034','BANCO PATAGONIA S.A.','https://ebankpersonas.bancopatagonia.com.ar/eBanking/usuarios/login.htm','patagonia'),
	('00044','BANCO HIPOTECARIO S.A.','https://hb.hipotecario.com.ar/hbanking/','hipotecario'),
	('00045','BANCO DE SAN JUAN S.A.','https://hb.redlink.com.ar/bsj/login.htm','sanjuan'),
	('00060','BANCO DEL TUCUMAN S.A.','https://www.macro.com.ar/bancainternet/#','tucuman'),
	('00065','BANCO MUNICIPAL DE ROSARIO','https://hb.redlink.com.ar/bmros/login.htm','rosario'),
	('00072','BANCO SANTANDER RIO S.A.','https://www2.personas.santander.com.ar/obp-webapp/angular/#!/login','santander'),
	('00083','BANCO DEL CHUBUT S.A.','https://hb.redlink.com.ar/bancochubut/login.htm','chubut'),
	('00086','BANCO DE SANTA CRUZ S.A.','https://hb.redlink.com.ar/bancosantacruz/login.htm','santacruz'),
	('00093','BANCO DE LA PAMPA SOCIEDAD DE ECONOMÍA MIXTA','https://hb.redlink.com.ar/bancodelapampa/login.htm','lapampa'),
	('00094','BANCO DE CORRIENTES S.A.','https://hb.redlink.com.ar/bancodecorrientes/login.htm','corrientes'),
	('00097','BANCO PROVINCIA DEL NEUQUÉN SOCIEDAD ANÓNIMA','https://hb.redlink.com.ar/bpn/login.htm','neuquen'),
	('00150','HSBC BANK ARGENTINA S.A.','https://www.online-banking.hsbc.com.ar/','hsbc'),
	('00191','BANCO CREDICOOP COOPERATIVO LIMITADO','https://bancainternet.bancocredicoop.coop/bcclbi/','credicoop'),
	('00198','BANCO DE VALORES S.A.','https://e.bancodevalores.com/bcovalores/View/Common/Login.aspx?ReturnUrl=%2fbcovalores%2f','valores'),
	('00247','BANCO ROELA S.A.','https://hb.redlink.com.ar/bancoroela/login.htm','roela'),
	('00254','BANCO MARIVA S.A.','https://hb.redlink.com.ar/mariva/login.htm','mariva'),
	('00259','BANCO ITAU ARGENTINA S.A.','https://internet.itau.com.ar/internet/sso','itau'),
	('00262','BANK OF AMERICA, NATIONAL ASSOCIATION','https://www.bankofamerica.com/es/','boamerica'),
	('00268','BANCO PROVINCIA DE TIERRA DEL FUEGO','https://hb.redlink.com.ar/btf/login.htm','tierradelfuego'),
	('00269','BANCO DE LA REPUBLICA ORIENTAL DEL URUGUAY','https://www.canales.brou.com.uy/eBanking/seguridad/loginFlow.htm?execution=e1s1','brou'),
	('00277','BANCO SAENZ S.A.','https://hb.redlink.com.ar/bancosaenz/login.htm','saenz'),
	('00281','BANCO MERIDIAN S.A.','https://hb.redlink.com.ar/bancomeridian/login.htm','meridian'),
	('00285','BANCO MACRO S.A.','https://www.macro.com.ar/bancainternet/#','macro'),
	('00299','BANCO COMAFI SOCIEDAD ANONIMA','https://hb.comafi.com.ar/homebank/HBI.do','comafi'),
	('00301','BANCO PIANO S.A.','https://hb.redlink.com.ar/bancopiano/login.htm','piano'),
	('00309','BANCO RIOJA SOCIEDAD ANONIMA UNIPERSONAL','https://hb.redlink.com.ar/nblr/login.htm','rioja'),
	('00310','BANCO DEL SOL S.A.','http://www.bdsol.com.ar/','bdsol'),
	('00311','NUEVO BANCO DEL CHACO S. A.','https://hb.redlink.com.ar/nbch/login.htm','chacho'),
	('00312','BANCO VOII S.A.','https://hb.redlink.com.ar/voii/login.htm','voii'),
	('00315','BANCO DE FORMOSA S.A.','https://hb.redlink.com.ar/bancodeformosa/login.htm','formosa'),
	('00319','BANCO CMF S.A.','https://bee.redlink.com.ar/cmf2','cmf'),
	('00321','BANCO DE SANTIAGO DEL ESTERO S.A.','https://hb.redlink.com.ar/bse/login.htm','santiago'),
	('00322','BANCO INDUSTRIAL S.A.','https://id.bind.com.ar/cas/login?service=https%3A%2F%2Fb24.bind.com.ar%2F','industrial'),
	('00330','NUEVO BANCO DE SANTA FE SOCIEDAD ANONIMA','https://hb.redlink.com.ar/bancobsf/login.htm','santafe'),
	('00331','BANCO CETELEM ARGENTINA S.A.','https://portal.cetelem.com.ar/?name=nuevoPortal','cetelem'),
	('00340','BACS BANCO DE CREDITO Y SECURITIZACION S.A.','https://accesoweb.bacs.com.ar/Login/Login?ReturnUrl=%2f','bacs'),
	('00341','BANCO MASVENTAS S.A.','https://hb.redlink.com.ar/bancomasventas/login.htm','masventas'),
	('00384','WILOBANK S.A.','https://banco.wilobank.com/wilobank/#/login','wilobank'),
	('00386','NUEVO BANCO DE ENTRE RÍOS S.A.','https://hb.redlink.com.ar/bancoentrerios/login.htm','bersa'),
	('00389','BANCO COLUMBIA S.A.','https://hbsrv.bancocolumbia.com.ar/html/Inicio.html','columbia'),
	('00426','BANCO BICA S.A.','https://hb.redlink.com.ar/bancobica/login.htm','bica'),
	('00431','BANCO COINAG S.A.','https://hb.redlink.com.ar/bancocoinag/login.htm','coinag'),
	('00432','BANCO DE COMERCIO S.A.','https://bee.redlink.com.ar/bcocomercio','comercio'),
	('44077','COMPAÑIA FINANCIERA ARGENTINA S.A.','https://efectivosionline.efectivosi.com.ar/html/Inicio.html','efectivosi'),
	('44092','FCA COMPAÑIA FINANCIERA S.A.','http://www.fcafinanciera.com.ar/ingresar.aspx','fcafinanciera'),
	('44093','GPAT COMPAÑIA FINANCIERA S.A.U.','https://micuenta.gpat.com.ar/webclientes/','gpat'),
	('44098','PSA FINANCE ARGENTINA COMPAÑÍA FINANCIER','https://www.psafinance.com.ar/psa/conexionEmpresa.do','psa'),
	('44100','FINANDINO COMPAÑIA FINANCIERA S.A.','https://hb.redlink.com.ar/finandino/login.htm','finandino'),
	('65203','CAJA DE CREDITO "CUENCA" COOPERATIVA LIMITADA','https://hb.redlink.com.ar/cccuenca/login.htm','regional');
  -- ('00305','BANCO JULIO SOCIEDAD ANONIMA','',''),
  -- ('00300','BANCO DE INVERSION Y COMERCIO EXTERIOR','',''),
  -- ('00266','BNP PARIBAS','',''),
  -- ('00165','JPMORGAN CHASE BANK, NATIONAL ASSOCIATION','',''),
  -- ('44088','VOLKSWAGEN FINANCIAL SERVICES COMPAÑIA F','','',''),
	-- ('44090','CORDIAL COMPAÑÍA FINANCIERA S.A.','','',''),
  -- ('44094','MERCEDES-BENZ COMPAÑÍA FINANCIERA ARGENT','','',''),
	-- ('44095','ROMBO COMPAÑÍA FINANCIERA S.A.','','',''),
	-- ('44096','JOHN DEERE CREDIT COMPAÑÍA FINANCIERA S.','','',''),
  -- ('44099','TOYOTA COMPAÑÍA FINANCIERA DE ARGENTINA','','',''),
  -- ('44059','FORD CREDIT COMPAÑIA FINANCIERA S.A.','','',''),
  -- ('00143','BRUBANK S.A.U.','',''),
  -- ('00147','BANCO INTERFINANZAS S.A.','',''),
  -- ('00332','BANCO DE SERVICIOS FINANCIEROS S.A.','','',''),
  -- ('00336','BANCO BRADESCO ARGENTINA S.A.U.','','',''),
  -- ('00338','BANCO DE SERVICIOS Y TRANSACCIONES S.A.','','',''),
  -- ('00339','RCI BANQUE S.A.','','',''),
  -- ('45056','MONTEMAR COMPAÑIA FINANCIERA S.A.','','',''),
	-- ('45072','TRANSATLANTICA COMPAÑIA FINANCIERA S.A.','','',''),
