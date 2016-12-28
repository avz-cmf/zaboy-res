# DI - InsideConstruct

##Быстрый старт

###Обычная практика

Пусть у нас есть класс, принимающий 3 сервиса в качестве зависимостей:

```
    class Class1
    {
        public $propA;
        public $propB;
        public $propC;

        public function __construct($propA = null, $propB = null, $propC = null)
        {
            $this->propA = $propA;
            $this->propB = $propB;
            $this->propC = $propC;
        }
    }

    /* @var $contaner ContainerInterface */
    global $contaner;
    $propA = $contaner->has('propA') ? $contaner->get('propA') : null;
    $propB = $contaner->has('propB') ? $contaner->get('propB') : null;
    $propC = $contaner->has('propC') ? $contaner->get('propC') : null;

    new Class1($propA, $propB, $propC);
```

Мы получили из контейнера зависимости и присвоили их одноименным свойствам объекта.

###Теперь то-же самое с использованием `InsideConstruct::initMyServices()`:

Если имя параметра соответствует имени сервиса и имени свойства объекта:

```
    class Class1
    {

        public $propA;
        public $propB;
        public $propC;

        public function __construct($propA = null, $propB = null, $propC = null)
        {
            InsideConstruct::initMyServices();
        }

    }

    new Class1();
```

Все три сервиса будут инициализированы сервисами из `$containr` как в примере выше.  
Вызов `InsideConstruct::initMyServices()` не изменяет переданные в констрактор параметры.  
Если у параметров констрактора указаны тип или интерфейс, то сервисы, полученные вызовом 
`InsideConstruct::initMyServices()` будут проверены на соответствие.  
Инициализируются `Public`, `Protected`, и `Private` свойства объекта. Не инициализируются `Static` свойства и `Private` свойства предков.
 
##Использование

### Что возвращает метод I`nsideConstruct::initMyServices();`
Возвращается массив `['param1Name'=>value1', 'param2Name' => 'value2', ...]`

### Как перекрыть умолчания
Если так:

            new Class1(new stdClass(), null);
то только один (последний) параметр будет инициализирован сервисом `$contaner->get('propC')`.  
Два других получат значения `new stdClass(`) и `null`. Но присваивания свойствам объекта или вызовы сеттеров (см. далее) отработают для всех параметров. 


### Сеттеры  (`$this->setPropA($value)`)
Если для параметра констрактора определен соответствующий (по имени) сеттер - он будет вызван.
Сеттеры имеют приоритет над свойствами. Если для параметра есть и сеттер и свойство, то будет вызван сеттер, а присваивание свойству не будет произведено.

### А если наследование?
Предположим у нас есть базовый класс:
```
	class Class0
	{
		public $propA;
	
	    public function __construct($propA = null)
	    {
	        InsideConstruct::initMyServices();
		}
	}

	$class0 = new Class0;        // $class0->propA = $container->get('propA');
```
, а нам нужно изменить используемый сервис:  
    ``` 
        $propB = $container->get('propB');
        $class0->propA = $propB->getPropA()
    ```  
Можно так:

```
	class Class1 extends Class0
	{
	    public function __construct($propB = null)
	    {
	            $params = InsideConstruct::initMyServices();
	            $propA = ($params['propB'])->getPropA();
				InsideConstruct::initParentService(['propA' => $propA]);
		}
	};
```
Так же, допустим если у нас есть класс 

```
	class Class0
	{
		public $propA;
		public $propB;
	
	    public function __construct($newPropA = null, $propB= null)
	    {
	            InsideConstruct::initMyServices();
		}
	}

	$class0 = new Class0;        // $class0->propA = $container->get('propA');
```
, а нам нужно изменить используемый сервис:  

```
 $class0->propA = $container->get('newPropA');
 $class0->propB = $container->get('propB');
```
  
Можно так:

```
	class Class1 extends Class0
	{
	    public function __construct($newPropA = null)
	    {
	          InsideConstruct::init(['newPropA' => 'propA']);  
		}
	};
```

Мы моежем использовать метод `initParentService()` для того что бы инициализировать родительские зависимости через конструктор,
Так же мы можем передавать в них массив содержащий те поля которые мы явно хотим передать в конструктор родительского класcа.

Или же используя метод `init()` мы можем инициализировать наши зависимости и зависимости родительского класа.
Если в конструкторе нашего класcа имеется имя того же сервиса что и в конструкторе родительского класса то 
зависимость пробрасывается - будет передана в конструктор родителя в качестве параметра.
Так же метод `init()` пренимает массив подстановки параметров, в случае если наследник переопределяет зависимость родителя.
В такком случае можно передать массив в котором ключ будет содержать имя переопределенной зависимости, а значение имя изначальной зависимости.

### Параметры вызова
В прошлом примере `InsideConstruct::initMyServices(['newPropA']);` добавлен параметр вызова 'newPropA'.  
Зачем это нужно? Дело в том, что у объекта `Class1` нет ни свойства `$this->newPropA`, 
ни метода `$this->setNewPropA()`. И он не будет пытаться загрузить сервис 'newPropA' в случае , если параметр не передан в констрактор.  
Передача параметра `InsideConstruct::initMyServices(['newPropA']);` явно указывает на `initMyServices()` загрузить сервис `'newPropA'`.

### Еще раз коротко о главном
Если есть соответствующий сеттер или свойство - значение будет присвоено.   
Если параметр передан (даже если `NULL`) - сервис из контейнера загружен не будет.   
Если параметр не передан, сервис из контейнера буде загружен если есть сеттер или свойство.   

