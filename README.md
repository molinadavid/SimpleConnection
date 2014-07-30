# SimpleConnection #

Version: 1.1

-------------------------------------------------

1. [__Installing__](#1-installing)
2. [__License__](#2-license) 
3. [__Setup__](#3-setup)  
4. [__Usage__](#4-usage)  
4.1. Query
4.2. Join
4.3. Insert  
4.4. Update  
4.5. Free query 

-------------------------------------------------

### Dependencies

- PHP >= 5.3
- PDO

-------------------------------------------------

## 1. Installing

Just include "SimpleConnection/SimpleConnection.php"

```php
require_once __DIR__.'/SimpleConnection/SimpleConnection.php';
```

-------------------------------------------------

## 2. License

SimpleConnection is licensed under ([The MIT License (MIT)](http://opensource.org/licenses/MIT))

The MIT License (MIT)

Copyright (c) 2014 David Molina ([molinadavid@hotmail.co.uk](mailto:molinadavid@hotmail.co.uk))

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

-------------------------------------------------

## 3. Setup

All the relevant configurations are in 'SimpleConnection/.config.inc.php'

```php
    public $default = array(
        'datasource' => 'Database/Mysql',
        'persistent' => false,
        'host' => 'localhost',
        'port' => '',
        'login' => '<Your user name>',
        'password' => '<Your password>',
        'database' => '<Your DB>',
        'schema' => '',
        'prefix' => '',
        'encoding' => 'utf8'
    );
```
-------------------------------------------------

## 4. Usage:

In order to use 'SimpleConnection' simply instantiate a new class of 'SimpleConnection' for every query and pass the name of the table as a parameter.
If you plan to join tables give the main table as the first parameter.

```php
$connect = new simpleConnection('table1');
```

The ```->where()```, ```->sc_and()``` and ```->sc_or()``` will accepts as parameters arrays or arrays of arrays if you wish to encapsulate the query.

```php
$connect = new simpleConnection('users');
$connect->select()
    ->where(array('id', '=', '10'));
$result = $connect->run();
```
The above example would be the same as:
```sql
SELECT
    *
FROM
    users
WHERE
    id = 10
```

Or it could also be like this:
```php
$connect = new simpleConnection('users');
$connect->select()
    ->where(array(array('id', '=', '10', 'and'), array('name', '!=')));
$result = $connect->run();
```
This example would give the follow result:
```sql
SELECT
    *
FROM
    users
WHERE
    (id = 10 AND name != '')
```
Note that if the value is omitted in the array the library will assume an empty string.


### 4.1. Query

#### Select

The select attributes can include the fields to select and the amount of rows to fetch.

The values for the fields to select can be one of the follow:
- @string 'all' = Will select all the fields on the table
- @string '*' = Will select all the fields on the table
- @string 'field1, field2, field3' = Will select only the specified fields
- @array array('field1', 'field2', 'field3') = Will select only the specified fields

The values for the rows to fetch can be one of the follow:
- @string 'all' = Will return an array of associative arrays with all the fetched rows
- @string 'single' = Will return a single associative array with a single fetched field, if more than one field
    matches the query only the first one will be returned.

If nothing is provided as parameters the default behaviour is to assume ->select('all', 'all')

```php
$connect = new simpleConnection('users');
$connect->select()
    ->where(array(array('id', '=', '10', 'and'), array('name', '!=')));
$result = $connect->run();

$connect = new simpleConnection('users');
$connect->select()
        ->where(array('id','=','10'))
        ->sc_and(array('name','!='))
        ->sc_or(array(array('id','=','11', 'and'), array('surname', '!=')))
        ->set_order('ORDER BY id ASC');
$result = $connect->run();
```
This example would give the follow result:
```sql
SELECT
    *
FROM
    users
WHERE
    id = 10
AND
    name != ''
OR
    (id = 11 AND surname != '')
ORDER BY id ASC
```

-------------------------------------------------

### 4.2 Join

To Join tables select the type of join and give the parameters.
Currently support:
    ->innerJoin(),
    ->leftJoin().
    
(the parameters must be given in an array)


```php
$connect = new simpleConnection('users');
$connect->select()
    ->innerJoin(array(office, office.id, '=', users.id'))
    ->where(array('users.id','=','10'));
$result = $connect->run();
```

-------------------------------------------------

### 4.3. Insert

To insert simply provide an array with the field names and the values to insert.

```php
$connect = new simpleConnection('users');
$connect->insert(array('id'=>'10', 'name'=>'John', 'Surname'=>'Doe'));
```
The insert function will return the last inserted Id when possible.

-------------------------------------------------

### 4.4. Updating

The update function works similar to 'select' but providing the fields and values to update as an array.

```php
$connect = new simpleConnection('users');
$connect->update(array('id'=>'10', 'name'=>'John'))
        ->where(array('mail','=','doe@exapmle.com'))
        ->run();
```


-------------------------------------------------

### 4.5. Free query

As this library is still under development and is being improved there is a function use it with those statements that are not yet supported without compromising on the security of the PDO statements.

```php
$connect = new simpleConnection();
$result = $connect->freeQuery('SELECT * FROM users WHERE id = :id', array('id'=>'10'));
```