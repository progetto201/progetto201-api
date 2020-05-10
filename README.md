# PROGETTO201-API
This is the backend api of the "progetto201" project.

## Sezioni
* [Introduzione](#Introduzione)
* [Guida all'uso](#guida-all-uso)
* [Descrizione](#descrizione)
* [Sviluppo e espansione](#sviluppo-e-espansione)
* [Requisiti](#requisiti)
* [Changelog](#changelog)
* [Autore](#autore)

## Introduzione
Gli script si occupano di gestire i dati provvenienti
dalla frontend e di gestirli inserendo/aggiornando/rimuovendo dati sul database.

## Guida all'uso
Importare in MySQL il database con tutte le tabelle con il file ```db100_100.sql```

Posizionare la cartella ```api``` (con tutto il suo contenuto)
nella cartella servita dal server web (per apache ```/var/www/html```)

## Descrizione
Per la documentazione di tutti gli script andare in [questa pagina](https://progetto201.github.io/progetto201-api/doc/html/files.html)

## Sviluppo e espansione

### Aggiungere planimetrie
Per aggiungere planimetrie non e' necessario
modificare righe di codice:
basta inserire nella cartella delle planimetrie
il documento svg e selezionarlo dall'interfaccia

### Aggiungere colori dell'interfaccia
Attualmente non e' possibile aggiugere
colori dell'interfaccia dall'interfaccia stessa:
occorre aggiungere un record nella tabella ```t_colors``` del database.

### Aggiungere/togliere label alla planimetria
E' possibile modificare i label direttamente dall'interfaccia
nella pagina delle impostazioni

### Aggiungere nuovi tipi di sensore al sistema
Per aggiungere nuovi sensori al sistema, oltre
ad adattare lo script [mqtt_manager](https://github.com/progetto201/mqtt_manager),
occorre aggiungere allo script ```/api/sensors/columnnames.php``` i nuovi tipi.

Esempio, alla prima versione l'api accetta un sensore, il tipo "0":
```php
$nodetypes = array("0" => "t_type0_data");
```
Se si dovessero aggiungere due nuovi tipi occorre aggiungerli all'array in questo modo:
```php
$nodetypes = array("0" => "t_type0_data", "1" => "t_type1_data", "2" => "t_type2_data");
```

Poi occorre anche aggiungere i nuovi tipi allo script ```/api/sensors/data.php```:
1. andare nella funzione ```getData()```, e aggiungere uno/piu' elseif nel punto:
    
```php
if ($nodetype === 0){
    $action_res = getDataType0($t_conn_res, $nodeid, $min_timestamp, $max_timestamp);
}
else{
    // errore: tipo non riconosciuto
    array_push($action_res['errors'], array('id' => 930,
                                            'htmlcode' => 422,
                                            'message' => "can't get data for this node type (not supported)"));
}
```

Ad esempio:
    
```php
if ($nodetype === 0){
    $action_res = getDataType0($t_conn_res, $nodeid, $min_timestamp, $max_timestamp);
}
elseif ($nodetype === 1){
    $action_res = getDataType1($t_conn_res, $nodeid, $min_timestamp, $max_timestamp);
}
elseif ($nodetype === 2){
    $action_res = getDataType2($t_conn_res, $nodeid, $min_timestamp, $max_timestamp);
}
else{
    // errore: tipo non riconosciuto
    array_push($action_res['errors'], array('id' => 930,
                                            'htmlcode' => 422,
                                            'message' => "can't get data for this node type (not supported)"));
}
```

2. Creare tante funzioni quanti sono i tipi prendendo come riferimento la funzione ```getDataType0()```

E infine modificare lo script ```/api/sysinfos/rssi.php```:
aggiungere alla variabile:

```php
/// Array with data tables
$data_tables = array("t_type0_data");
```
i nuovi tipi.
Esempio:
```php
/// Array with data tables
$data_tables = array("t_type0_data", "t_type1_data", "t_type2_data");
```

## Requisiti
* php
* server web
* mysql

## Changelog

**01_01 2020-05-10:** <br>
Primo commit

## Autore
Zenaro Stefano