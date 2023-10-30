# process-manager

---

English|[中文](README_zh.md)



---


### Granulate your process so that it can be managed in a more elegant way.

---

Process-manager is a logical process management tool designed to granulate the logical processes in code. Each logical process in a granule is called a logical segment, and all logical segments in the entire process chain are called a logical chain.

After granulation, each independent logical segment can be managed. Before the entire logical chain is executed, we can replace, enable/disable, and add pre/post logical operations to the previously defined logical segments.

Why should I do this? The emergence of tools is inevitably to solve a certain type of problem. I assume that you have heard of and understand some event or hook mechanisms in frameworks. Some frameworks also have designed AOP aspect mechanisms and middleware mechanisms. Their implementation and usage have their own advantages, but the problems they ultimately solve can be summarized as decoupling or, in other words, separating the underlying layer from the business layer, making it possible for the business to expand from the underlying layer through these mechanisms. This is also what process-manager does.

## Core Concepts

---

### Logical Segment

---

> A logical segment represents the smallest unit of process control. It can be an expression, a loop statement, or any other form of logic. Each logical segment is managed by a dedicated object represented as `LogicAbstract`. You can inherit from this object to write custom logic implementations, or simply instantiate `CallableLogic` and pass a `callable` function to achieve the desired logic.

### Logical Chain

---

> The logical chain is essentially an array that contains multiple instances of `LogicAbstract`. When you invoke the `executeLogics` method, it will execute the logical chain. If all logical segments are executed successfully without returning false, we consider the entire logical chain to be successful. The final result of the logical chain, which can be obtained using the getResult method, should be `true`.
> If any logical segment returns `false` or throws an exception during execution, the logical chain will be terminated, and the result will be `false`. Additionally, you can use the getResultMessage method to retrieve the error message corresponding to the failed logical segment.

### Granulation

---

> The process of implementing this mechanism is referred to as "granulation."



## Usage

---

### Installation using Composer

---

```bash
composer require coco-project/process-manager --no-dev
```

### get started

---

``` php

    use Coco\processManager\CallableLogic;
    use Coco\processManager\ProcessRegistry;

    require '../vendor/autoload.php';

    // Get process manager instance.
    $registry = new ProcessRegistry();

    // Set whether it is in debug mode.
    $registry->setIsDebug(true);

    // Register the first logical segment.
    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('apendLogic_1-debugMsg');
        $logic->setMsg('apendLogic_1-msg');

        echo 'missionName : ' . $logic->getName();
        echo PHP_EOL;

        //return false;
    }, 'apendLogic_1'));

    // Register the second logical segment.
    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('apendLogic_2-debugMsg');
        $logic->setMsg('apendLogic_2-msg');

        echo 'missionName : ' . $logic->getName();
        echo PHP_EOL;

        return false;
    }, 'apendLogic_2'));

    // Execute all registered logical segments.
    $registry->executeLogics();

    // If all the logical segments have been executed and none of them returned false, we consider the entire logic chain to have been executed successfully. Therefore, the result here should be true.
    // If any of the logical segments return false or throw an exception during execution, the logic chain will be terminated. Therefore, the result here should be false.
    var_dump($registry->getResult());

    // Get the information after execution is completed.
    // If a logical segment returns false, this method returns the corresponding information set by that logical segment. If none of the logical segments return false, this method returns the information set by the last logical segment.
    // The information is set using the setDebugMsg or setMsg method in the above logical segment.
    // If setIsDebug is true, the information returned is from setDebugMsg.
    // If setIsDebug is false, the information returned is from setMsg.
    echo $registry->getResultMessage();
    echo PHP_EOL;
``` 

### Why is it necessary to do this?

---

#### Let's consider a business scenario, a simple login process.


Many application plugins are implemented using mechanisms such as events, middleware, AOP, etc. It must be acknowledged that these ideas are forward-thinking and elegant, solving many problems. However, in certain scenarios, they lack a certain level of flexibility. Let's take a look at the following scenario:

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

    // Some operations may be required here
    //* Add login log
    //* Update user login information
    //* Record login status
    //* Dynamically calculate corresponding permissions based on user information
    //* Send emails or messages, etc.
    //* And so on

    //Assuming that the above logic has been implemented in the underlying layer of your application
    
    // In order to extend for other developers, we may listen to a login success event below and leave it to other developers to define their own required logic
    // After other developers have written the logic to be inserted, they can associate on_login_success with the corresponding logic, and trigger it with the following code:
    eventManager::listen('on_login_success');

    exit($result);
```

In most cases, there is no problem with writing it this way. However, if you intend to encapsulate your logic and provide an interface for other developers to extend upon it, once the above logic is encapsulated in the underlying layer, other developers will not be able to modify it. You can try using a process-manager to solve this problem.

```php
<?php

    use Coco\processManager\CallableLogic;
    use Coco\processManager\ProcessRegistry;

    require '../vendor/autoload.php';

    $registry = new ProcessRegistry();

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
        // Add login log
    }, 'logic_8'));

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        // Update user login information
    }, 'logic_9'));

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        // Record login status
    }, 'logic_10'));

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        // Dynamically calculate corresponding permissions based on user information
    }, 'logic_11'));

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        // Send emails or messages, etc.
    }, 'logic_12'));

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        //添加事件监听
        eventManager::listen('on_login_success');
    }, 'logic_13'));

    /**
     ****************************************************************
     ****************************************************************
     */
     
    // Assume the above logic is the fixed underlying logic of your framework
    // From here, until the execution of $registry->executeLogics() below, can be exposed to other developers
    // They can perform management operations on the logic by using $registry here
    
    
    // Replace the logic segment of logic_6 above
    // After replacement, the logic segment of logic_6 above will not be executed, instead, the logic segment here will be executed
    $registry->replaceLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        echo 'logicName : replaced : ' . $logic->getName();
        echo PHP_EOL;

        // It is even possible to use this method to disable specific logic segments, and the logic segment of logic_10 will not be executed.
        $registry->setLogicStatus('logic_10', false);

        //return false;
    }, 'logic_6'));

    // Insert this logic segment before the logic segment of logic_5 above
    // The execution order will be ... logic_4, logic_5_before, logic_5 ...
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
    
    // Determine the handling based on the result of execution.
    if ($registry->getResult())
    {
        //...
    }
    else
    {
        //...
    }

    // Or directly respond.
    $result = json_encode([
        "code" => $registry->code,
        "msg"  => $registry->getResultMessage(),
    ]);
    exit($result);

```

### Parameter passing between logical segments.

---

You may have noticed in the example above that there is a way to pass parameters between logical paragraphs. Both `ProcessRegistry` and `LogicAbstract` implement the saving of variables through the `__set` and `__get` methods.
```php
    $registry->variableName = 'value';
```


### API

---

> For more examples, please refer to the "examples" folder.

#### ProcessRegistry 

---

##### setIsDebug(bool $isDebug): static
```php
    // If `setIsDebug()` is set to true, `$registry->getResultMessage()` will return the message set by `$logic->setDebugMsg()`.
    // If `setIsDebug()` is set to false,` $registry->getResultMessage()` will return the message set by `$logic->setMsg()`.
    // The default value is false.
    //Must be called before `executeLogics()`
```

##### isDebug(): bool
```php
    //Checking the debug status.
```

##### setOnStart(?LogicAbstract $onStart): static
```php
    // The logic segment registered by setOnStart() will be called before the entire logic chain is executed, regardless of the return value of setOnStart().
    //Must be called before `executeLogics()`
```

##### setOnDone(?LogicAbstract $onDone): static
```php
    // The logic segment registered by setOnDone() will be called when the entire logic chain is executed and no exception is thrown.
    //Must be called before `executeLogics()`
```

##### setOnCatch(?LogicAbstract $onCatch): static
```php
    // The logic segment registered by setOnCatch() will be called when an exception is thrown during the execution of the entire logic chain, and $registry->getResultMessage() will return the message of the thrown exception.
    //Must be called before `executeLogics()`
```

##### setOnResultIsTrue(?LogicAbstract $onResultIsTrue): static
```php
    // The logic segment registered by setOnResultIsTrue() will be called when $registry->getResult() is true.
    //Must be called before `executeLogics()`
```

##### setOnResultIsFalse(?LogicAbstract $onResultIsFalse): static
```php
    // The logic segment registered by setOnResultIsFalse() will be called when $registry->getResult() is false, regardless of whether an exception is thrown or not.
    //Must be called before `executeLogics()`
```

##### apendLogic(LogicAbstract $logic): static
```php
    // To append a logic segment to the end of the entire logic chain, similar to array_push
    //Must be called before `executeLogics()`
```

##### prependLogic(LogicAbstract $logic): static
```php
    // To add a logic segment to the beginning of the entire logic chain, similar to array_unshift
    //Must be called before `executeLogics()`
```

##### injectLogicBefore(LogicAbstract $logic, string $logicName): static
```php
    //Inserts a logic segment before the logic segment $logicName
    //Must be called before `executeLogics()`
```

##### injectLogicAfter(LogicAbstract $logic, string $logicName): static
```php
    //Inserts a logic segment after the logic segment $logicName
    //Must be called before `executeLogics()`
```

##### replaceLogic(LogicAbstract $logic): static
```php
    //Replaces a logic segment with the same name in the logic chain
    //Must be called before `executeLogics()`
```

##### executeLogics(): bool
```php
    //Executes the logic chain
```

##### totalLogics(): int
```php
    //Gets the number of logic segments in the current logic chain
```

##### getLogicList(): array
```php
    //Gets the current logic chain
```

##### getResult(): bool
```php
    //Gets the execution result of the current logic chain
    //Must be called after `executeLogics()`
```

##### getErrorLogic(): ?LogicAbstract
```php
    //Gets the logic segment in which an error occurred during the execution of the logic chain
    //Must be called after `executeLogics()`
```

##### getInvokedLogics(): ?LogicAbstract
```php
    //Gets all the logic segments that have been executed
```

##### getResultMessage(): string
```php
    //Gets the information set by the logic segment in case of an error, or the exception message thrown by the logic segment
    //Must be called after `executeLogics()`
```

##### setLogicStatus(string $logicName, bool $isEnable): static
```php
    //Sets whether the logic segment with the specified $logicName should be executed when calling executeLogics()
    //Since this method can be called within a logic segment, it only takes effect on the logic segments after the current one
    //For example, if the logic chain currently has logic segments A, B, C, D, and E
    //Calling this method in logic segment C to disable logic segment D will take effect  
    //Calling this method in logic segment C to disable logic segment B will not take effect, because logic segment B has already been executed before logic segment C  
```

##### if(LogicAbstract $condition, LogicAbstract $ifCallback, ?LogicAbstract $elseCallback = null): static
```php
    //Implements if-else logic and allows for unlimited nesting
    //The first parameter is the conditional logic segment. If it does not return false or does not return at all, the second parameter's logic segment is executed. If it returns false, the third parameter's logic segment is executed
    //The second parameter is the logic segment to be executed when the condition returns a value other than false
    //The third parameter is the logic segment to be executed when the condition returns false. It can be omitted
    $registry->if(
        CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {}, 'ifCondition'),
        
        CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {}, 'ifCallback'),
        
        CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {}, 'elseCallback')
    )
```

#### LogicAbstract 

---

> You may come across some other methods in the source code that are not listed here. In fact, those methods do not need to be called by you. They will be called internally within the ProcessRegistry, so you don't have to worry about them.

```php
    CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {

        //$registryset->IsDebug() is true, $registry->getResultMessage() returns the information set by $logic->setDebugMsg
        //$registryset->setIsDebug() is false, $registry->getResultMessage() returns the information set by $logic->setMsg
        $logic->setDebugMsg('debugMsg');
        $logic->setMsg('msg');

        //If you are not calling through a CallableLogic instance, but instead writing logic segments by inheriting LogicAbstract, you will need this method to get $registry
        $logic->getRegistry();

        //Get logicName
        $logic->getName();
        
        //return false;
    }, 'logicName'));
```


## License

---

The MIT License (MIT).
