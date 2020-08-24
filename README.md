# status-filter-behave
Typical helper for show status-drop-list and validate sequence of them

Why it's need for you
-
Extension filter statuses for model by array of allowed statuses by sequence or/and permissons.
Your model should has additional table of status history and corresponding relation methods.

Installation
-
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

* Either run

```
php composer.phar require --prefer-dist "elmys/status-filter-behave" "*"
```

or add

```json
"elmys/status-filter-behave" : "*"
```

to the require section of your application's `composer.json` file.

Usage
-
In your model of statuses:
```php
// Import
use elmys\helpers\statusfilterbehave;

class YourStatusHistoryModel{

//constants section
const STATUS_ONE = 1; // status_one
const STATUS_TWO = 2; // status_two
const STATUS_THREE = 3; // status_three

const ALLOWED_STATUSES = [
        self::STATUS_ONE => [
            self::STATUS_TWO,
        ],
        self::STATUS_TWO => [
            self::STATUS_ONE,
            self::STATUS_THREE,
        ],
];

const STATUSES_BY_PERMISSIONS = [
        'permissionOne' => [
            self::STATUS_ONE,
        ],
];

//behave section
public function behaviors()
    {
        return [
            [
                'class' => StatusFilterBehave::class,
                'getParentMethodName' => 'order', // YourStatusHistoryModel->getOrder() = order
                //'sortListAsc' => false, // sort list of statuses
                //'parentStatusAttributeName' => 'current_status_id', // if general model store current status id and have different field name
                //'childStatusIdAttributeName' => 'status_id', // if model of statuses has different field name
                //'errorMsgEmptyStatus' => 'You must fill "Status"', // error message 1
                //'errorMsgWrongJumpStatus' => 'Incorrect sequence of statuses', // error message 2
                //'errorMsgPermission' => 'You haven\'t permission', // error message 3
            ],
        ];
    }
}
```

In activeForm view file:
```php
// $model - model of statuses
<?= $form->field($model, 'status_id')->dropDownList($model->getAvailableStatuses($model)) ?>
```