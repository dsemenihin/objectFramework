<?php
require_once 'conf.php';
require_once 'common.php';


/*
 * Небольшой фреймворк для работы с объектами и их списками
 * Своего рода, ActiveRecords
 * Одной из особенностей - это отсутствие необходимости дублировать в коде структуру 
 * постоянного хранилища. Т.е. для того, чтобы начать работать с новым типом объектов, с новой таблицой в БД,,
 * например Mysql, достаточно просто создать эту таблицу, код уже готов. В случае же MongoDB вообще ничего 
 * не требуется.
 * 
 * В случае необходимости виртруально существующие классы объектов можно явно определить в коде
 * и задать им любую фуннкциональность
 * 
 * 
 * Создание объекта.
 * В случае хранилища с предопределенной схемой коллекции(таблицы), например Mysql,
 * объект будет инициализирован списком полей с дефолтными значениями схемы + объекту будет присвоен
 * уникальный id
 * В случае отсутствия жесткой схемы, будет создана запись с уникальным id
 */
$campaign = CampaignObject::create();

/*
 * Загрузка существующего объекта
 */
$campaign = CampaignObject::create(2233); //будет загружен объект из коллекции(таблицы) CampaignObject с id=2233

/*
 * Изменение(определение свойств) объекта
 */
$campaign->setName('Контекстный переходы')->setBudgetLimit(100); //по одному полю
$campaign->setFields(array('name' => 'Контекстный переходы', 'budget_limit' => 100)); //массивом значений

/*
 * Получение свойств
 */
$campaign->getName();

/*
 * Сохранение объектов.
 * Происходит автоматически без явного вызова в случае, если свойства объекта были изменены.
 * Причем непосредственно сохранение происходит в деструкторе объекта-хранилища, что позволяет
 * минимизировать количество запросов, объединяя все объекты одной коллекции в один запрос.
 * 
 */
$campaign->save();//принудительно сохранить объект, до отработки деструктора

/*
 * Работа с несколькими хранилищами (разных типов или одного типа, но с разными настроками)
 * В конфиге задается список доступных адаптеров, а также хранилище по умолчанию
 */

CampaignObject::create(123, ObjectStorage::create('Mongo')); //загрузить объект из хранилища Mongo

/*
 * Кеширование
 * Включается на уровне хранилища в конфиге
 * Можно использовать разные адаптеры кеша
 * Кеш позволяет повторно не загружать объекты из базы.
 * Кеш отслеживает актуальность данных и обновляется по мере необходимости
 */

/*
 * Списки объектов
 * Реализованы интерфейсы ArrayAccess, Iterator
 */
$list = CampaignObject::getList(); //получить объект списка для всех объектов CampaignObject
$list = CampaignObject::getList(array('user_id' => 10)); //получить объект списка c условием

foreach (CampaignObject::getList() as $campaign){;} //перебор элементов
$campaign = $list[123]; //доступ к элементу списка с id 123

/*
 * Собственные списки
 */
$user = UserObject::create();
//создание списка объектов CampaignObject с критерием нахождения в нем 
$campaignList = $user->getCampaignObjectList(array('user_oid' => $user->getId()));

//добавление элемента в список
$campaign = CampaignObject::create();
//1. так
$campaign->setUserOid($user->getId());
//2. или так
$campaignList->addItem($campaign);

