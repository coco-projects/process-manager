# process-manager

---

[English](README.md)|中文


---

### 将你的流程颗粒化，让流程可以以更优雅的方式被管理

---

process-manager 是个逻辑流程管理工具，它被设计为将代码中的逻辑流程进行颗粒化（granulation），每个颗粒中的逻辑我们称之为逻辑段，整个流程链条中的所有逻辑段我们称之为逻辑链

颗粒化之后可以对每个每个独立逻辑段进行管理，在整个逻辑链被执行之前，我们可以对之前已经定义的逻辑段进行替换，启用/禁用，添加前置、后置逻辑等操作等等

为什们我要这么做？工具的出现必然是为了解决某类问题，我假设你听说过并理解一些框架中的事件或者钩子机制，有些框架也有设计 AOP 切面机制，中间件机制，他们的实现方式和使用方式各有千秋，但是最终解决的问题总结起来可以理解同样一件事：解耦，或者换个表达方式：底层与业务的剥离，让业务通过这些机制从底层上拓展成为可能。这也是 process-manager 做的事情

## 核心概念


---

### 逻辑段

---

> 代表流程控制的最小单元，可以是个表达式，可以是个循环语句等等，每个逻辑段用一个专门的对象来管理，这个对象表示为 `LogicAbstract`，可以继承它以后编写实现逻辑，也可以简单的通过实例化 `CallableLogic` 传入一个 `callable` 来实现

### 逻辑链

---

> 逻辑链本质是一个数组，里面包含多个 `LogicAbstract`，调用 `executeLogics` 会执行这个逻辑链，当全部逻辑段执行完，所有逻辑段都没有返回 false 时，我们认为整个逻辑链条执行成功，最终通过 `getResult` 获取到整个逻辑链的结果应该为 `true`，任何一个逻辑段执行后返回 false，或者抛出异常，逻辑链将被终止执行，结果将为 false，同时可以通过 `getResultMessage` 方法获取到对应逻辑段的错误信息

### 颗粒化

---

> 实现这套机制的过程，我们称其为颗粒化



## 用法

---

### 使用 Composer 安装

---

```bash
composer require coco-project/process-manager
```

### 入门示例

---

``` php

    use Coco\processManager\CallableLogic;
    use Coco\processManager\ProcessRegistry;

    require '../vendor/autoload.php';

    //获取过程管理器实例
    $registry = new ProcessRegistry();

    //设置是否debug模式
    $registry->setIsDebug(true);

    //注册第1个逻辑段
    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('apendLogic_1-debugMsg');
        $logic->setMsg('apendLogic_1-msg');

        echo 'missionName : ' . $logic->getName();
        echo PHP_EOL;

        //return false;
    }, 'apendLogic_1'));

    //注册第2个逻辑段
    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('apendLogic_2-debugMsg');
        $logic->setMsg('apendLogic_2-msg');

        echo 'missionName : ' . $logic->getName();
        echo PHP_EOL;

        return false;
    }, 'apendLogic_2'));

    //执行注册的所有逻辑段
    $registry->executeLogics();

    //当以上全部逻辑段执行完，逻辑段都没有返回 false 时，我们认为整个逻辑链条执行成功，此处结果为应该为 true
    //当有任何一个逻辑段执行后返回 false，或者抛出异常，逻辑链将被终止执行，此处结果为 false
    var_dump($registry->getResult());

    //获取执行完成后的信息
    //当有逻辑段返回 false 时，此处返回对应逻辑段设置的信息，当所有逻辑段都没返回 false 时，此处返回最后一个逻辑段设置的信息
    //信息通过上面逻辑段中的 setDebugMsg 或者 setMsg 方法设置
    //setIsDebug 为 true ，返回 setDebugMsg 的信息
    //setIsDebug 为 false ，返回 setMsg 的信息
    echo $registry->getResultMessage();
    echo PHP_EOL;
``` 

### 为什要这样？

---

#### 让我们看一个业务场景,一个简单的登录流程


太多的应用其插件实现机制都是通过事件，中间件，AOP 等方式实现拓展，必须承认，这些思想十分前卫和优雅，解决了很多问题，但是在某些场景中缺乏一定的灵活度，看看下面的场景：

``` php
    $result = null;

    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;
    $captcha  = $_POST['captcha'] ?? null;

    if (!usernameRoleCheck($username))
    {
        $result = json_encode([
            "code" => 0,
            "msg"  => 'Login credentials do not match.',
        ]);

        exit($result);
    }

    if (!passwordRoleCheck($username))
    {
        $result = json_encode([
            "code" => 0,
            "msg"  => 'Login credentials do not match.',
        ]);
        exit($result);
    }

    if (!captchaRoleCheck($captcha))
    {
        $result = json_encode([
            "code" => 0,
            "msg"  => 'Verification code does not match.',
        ]);
        exit($result);
    }

    $info = getUserInfoByUsername($username);

    if (!$info)
    {
        $result = json_encode([
            "code" => 0,
            "msg"  => 'Login credentials do not match.',
        ]);
        exit($result);
    }

    if (!$info['status'] == USER_STATUS_NORMAL)
    {
        $result = json_encode([
            "code" => 0,
            "msg"  => 'User status is abnormal, please contact the administrator.',
        ]);
        exit($result);
    }

    if (!checkPwd($info['pwd'], $password))
    {
        $result = json_encode([
            "code" => 0,
            "msg"  => 'Login credentials do not match.',
        ]);
        exit($result);
    }


    //finally
    $result = json_encode([
        "code" => 1,
        "msg"  => 'Login successful.',
    ]);

    //这里可能需要做一些操作
    //* 添加登录日志
    //* 更新用户登录信息
    //* 记录登录状态
    //* 根据用户信息动态计算相应权限
    //* 发送邮件或者短信等
    //* 等等

    //假设这里是在自己开发的框架中把以上逻辑都编写完成了

    //为了给其他开发者拓展，下面可能会监听一个登录成功的事件，留给其他开发者自己定义他们需要的逻辑
    //其他开发者写好要插入的逻辑以后，将 on_login_success 与对应逻辑关联起来，即可通过以下代码触发
    eventManager::listen('on_login_success');

    exit($result);
```

大部分情况下这样写没什么问题，但如果你有意将你的逻辑封装起来，对外提供接口，让其他开发者这在其基础之上拓展的话，以上逻辑一旦在封装在底层，其他开发者将无法插手修改，试试 process-manager 如何解决这个问题

```php
<?php

    use Coco\processManager\CallableLogic;
    use Coco\processManager\ProcessRegistry;

    require '../vendor/autoload.php';

    //获取过程管理器实例
    $registry = new ProcessRegistry();

    //设置是否debug模式
    $registry->setIsDebug(true);

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $registry->username = $_POST['username'] ?? null;
        $registry->password = $_POST['password'] ?? null;
        $registry->captcha  = $_POST['captcha'] ?? null;

        $registry->code = 1;

    }, 'logic_1'));

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('usernameRoleCheck ：Login credentials do not match.');
        $logic->setMsg('Login credentials do not match.');

        if (!usernameRoleCheck($registry->username))
        {
            $registry->code = 0;
            return false;
        }

    }, 'logic_2'));

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('passwordRoleCheck ：Login credentials do not match.');
        $logic->setMsg('Login credentials do not match.');

        if (!passwordRoleCheck($registry->password))
        {
            $registry->code = 0;
            return false;
        }

    }, 'logic_3'));

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('captchaRoleCheck ：Login credentials do not match.');
        $logic->setMsg('Login credentials do not match.');

        if (!captchaRoleCheck($registry->captcha))
        {
            $registry->code = 0;
            return false;
        }

    }, 'logic_4'));

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('user does not exist');
        $logic->setMsg('Login credentials do not match.');

        $info = getUserInfoByUsername($registry->username);

        if (!$info)
        {
            $registry->code = 0;
            return false;
        }

        $registry->info == $info;
    }, 'logic_5'));

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg(json_encode($registry->info));
        $logic->setMsg('User status is abnormal, please contact the administrator.');

        if ($registry->info['status'] !== USER_STATUS_NORMAL)
        {
            $registry->code = 0;
            return false;
        }

    }, 'logic_6'));

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('password incorrect');
        $logic->setMsg('Login credentials do not match.');

        if (!checkPwd($registry->info['pwd'], $registry->password))
        {
            $registry->code = 0;
            return false;
        }
    }, 'logic_7'));

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        //添加登录日志
    }, 'logic_8'));

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        //更新用户登录信息
    }, 'logic_9'));

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        //记录登录状态
    }, 'logic_10'));

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        //根据用户信息动态计算相应权限
    }, 'logic_11'));

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        //发送邮件或者短信等
    }, 'logic_12'));

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        //添加事件监听
        eventManager::listen('on_login_success');
    }, 'logic_13'));

    /**
     ****************************************************************
     ****************************************************************
     */

    //假设以上逻辑为你框架底层固定的逻辑
    //从这里，到下面的 $registry->executeLogics() 执行之前，都可以暴露给其他开发者
    //他们可以在此处通过 $registry 来对逻辑执行一些管理操作


    //替换上面的 logic_6 的逻辑段
    //替换以后，上面的 logic_6 逻辑段将不会被执行，而是执行此处的逻辑段
    $registry->replaceLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        echo 'logicName : replaced : ' . $logic->getName();
        echo PHP_EOL;

        //甚至可以通过此方法，将指定逻辑关闭，logic_10 逻辑段将不会被执行
        $registry->setLogicStatus('logic_10', false);

        //return false;
    }, 'logic_6'));

    //在上面的 logic_5 逻辑段之前插入这段逻辑
    //执行时顺序会是 ... logic_4，logic_5_before，logic_5 ...
    $registry->injectLogicBefore(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('logic_5_before-debugMsg');
        $logic->setMsg('logic_5_before-msg');
        echo 'logicName : logic_5_before : ' . $logic->getName();
        echo PHP_EOL;

        //return false;
    }, 'logic_5_before'), 'logic_5');

    $registry->prependLogic(/*...*/);
    $registry->apendLogic(/*...*/);
    $registry->setOnDone(/*...*/);
    $registry->injectLogicBefore(/*...*/);
    $registry->injectLogicAfter(/*...*/);
    $registry->replaceLogic(/*...*/);

    // ...

    /**
     ****************************************************************
     ****************************************************************
     */

    $registry->executeLogics();

    //通过执行的结果判断处理
    if ($registry->getResult())
    {
        //...
    }
    else
    {
        //...
    }

    //或者直接输出
    $result = json_encode([
        "code" => $registry->code,
        "msg"  => $registry->getResultMessage(),
    ]);
    exit($result);

```

### 逻辑段之间的参数传递

---

你可能有发现上面的示例中有这个写法，这是逻辑段之间的传递参数的方式，ProcessRegistry，LogicAbstract 都实现了通过 __set 和 __get 方法来保存变量

```php
    $registry->variableName = 'value';
```


### API

---

> For more examples, please refer to the "examples" folder.

#### ProcessRegistry 方法

---

##### setIsDebug(bool $isDebug): static
```php
    //setIsDebug() 为 true ，$registry->getResultMessage() 返回 $logic->setDebugMsg 的信息
    //setIsDebug() 为 false ，$registry->getResultMessage() 返回 $logic->setMsg 的信息
    //默认为 false
    //必须在 executeLogics() 之前调用
```

##### isDebug(): bool
```php
    //查看是否为 debug 状态
```

##### setOnStart(?LogicAbstract $onStart): static
```php
    //setOnStart 注册的逻辑段将会在整个逻辑链执行之前被调用，无论 setOnStart 返回什么，逻辑链都会被执行
    //必须在 executeLogics() 之前调用
```

##### setOnDone(?LogicAbstract $onDone): static
```php
    //setOnDone 注册的逻辑段将会在整个逻辑链执行结束并且未抛出异常时调用
    //必须在 executeLogics() 之前调用
```

##### setOnCatch(?LogicAbstract $onCatch): static
```php
    //setOnCatch 注册的逻辑段将会在整个逻辑链执行中抛出异常时被调用，$registry->getResultMessage() 将返回异常抛出的信息
    //必须在 executeLogics() 之前调用
```

##### setOnResultIsTrue(?LogicAbstract $onResultIsTrue): static
```php
    //setOnResultIsTrue 注册的逻辑段将会在 $registry->getResult() 为 true 时被调用
    //必须在 executeLogics() 之前调用
```

##### setOnResultIsFalse(?LogicAbstract $onResultIsFalse): static
```php
    //setOnResultIsFalse 注册的逻辑段将会在 $registry->getResult() 为 false 时被调用，无论是否抛出异常
    //必须在 executeLogics() 之前调用
```

##### apendLogic(LogicAbstract $logic): static
```php
    //为整个逻辑链最后追加一个逻辑段，效果类似 array_push
    //必须在 executeLogics() 之前调用
```

##### prependLogic(LogicAbstract $logic): static
```php
    //为整个逻辑链最前面添加一个逻辑段，效果类似 array_unshift
    //必须在 executeLogics() 之前调用
```

##### injectLogicBefore(LogicAbstract $logic, string $logicName): static
```php
    //为 $logicName 逻辑段之前插入一个逻辑段
    //必须在 executeLogics() 之前调用
```

##### injectLogicAfter(LogicAbstract $logic, string $logicName): static
```php
    //为 $logicName 逻辑段之后插入一个逻辑段
    //必须在 executeLogics() 之前调用
```

##### replaceLogic(LogicAbstract $logic): static
```php
    //替换逻辑链中同名的逻辑段
    //必须在 executeLogics() 之前调用
```

##### executeLogics(): bool
```php
    //执行逻辑链
```

##### totalLogics(): int
```php
    //获取当前逻辑链中逻辑段数量
```

##### getLogicList(): array
```php
    //获取当前逻辑链

```
##### getResult(): bool
```php
    //获取当前逻辑链执行结果
    //必须在 executeLogics() 之后调用
```

##### getErrorLogic(): ?LogicAbstract
```php
    //获取当前逻辑链执行出错的逻辑段
    //必须在 executeLogics() 之后调用
```

##### getInvokedLogics(): ?LogicAbstract
```php
    //获取所有已经被执行过的逻辑段
```

##### getResultMessage(): string
```php
    //获取执行出错的逻辑段设定的信息，或者是逻辑段抛出的异常信息
    //必须在 executeLogics() 之后调用
```


##### setLogicStatus(string $logicName, bool $isEnable): static
```php
    //设置指定 $logicName 的逻辑段在调用 executeLogics() 时是否执行
    //由于此方法可以在一个逻辑段中调用，所以只会对调用中的逻辑段之后的逻辑段生效
    //比如现在逻辑链中有 A,B,C,D,E 逻辑段
    //在 C 逻辑段中调用此方法，设置 D 逻辑段关闭，可以生效  
    //在 C 逻辑段中调用此方法，设置 B 逻辑段关闭，不会生效，因为 B 逻辑段已经在 C 逻辑段之前执行完成了  
```

##### if(LogicAbstract $condition, LogicAbstract $ifCallback, ?LogicAbstract $elseCallback = null): static
```php
    //实现了if else 逻辑，允许无限嵌套使用
    //第一个参数为条件逻辑段，没有返回或者不返回 false 时执行第二个参数的逻辑段，返回 false 时，执行第三个参数的逻辑段
    //第二个参数为条件返回不为 false 时执行的逻辑段
    //第三个参数为条件返回 false 时执行的逻辑段，可以不传
    $registry->if(
        CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {}, 'ifCondition'),
        
        CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {}, 'ifCallback'),
        
        CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {}, 'elseCallback')
    )
```


#### LogicAbstract

---

> 可能你会在源码中看到一些其他方法没写在这里，事实上那些方法并不需要你调用，它们会在 ProcessRegistry 内部被调用，所以不用关心

```php
    CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {

        //$registryset->IsDebug() 为 true ，$registry->getResultMessage() 返回 $logic->setDebugMsg 的信息
        //$registryset->setIsDebug() 为 false ，$registry->getResultMessage() 返回 $logic->setMsg 的信息
        $logic->setDebugMsg('debugMsg');
        $logic->setMsg('msg');

        //如果不是通过 CallableLogic 实例调用，而是继承 LogicAbstract 来编写逻辑段，会需要这个方法来获取 $registry
        $logic->getRegistry();

        //获取 logicName
        $logic->getName();
        
        //return false;
    }, 'logicName'));
```


## License

---

The MIT License (MIT).
