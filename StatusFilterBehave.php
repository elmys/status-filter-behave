<?php

/**
 * @link https://github.com/elmys/status-filter-behave
 * @copyright Copyright (c) 2020 elmys
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 */

namespace elmys\helpers\statusfilterbehave;

use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Typical helper for show status-drop-list and validate sequence of them.
 *
 * @author elmys
 * @package elmys\helpers\statusfilterbehave
 */
class StatusFilterBehave extends Behavior
{
    /** @var string */
    public $getParentMethodName;
    /** @var boolean */
    public $sortListAsc = false;
    /** @var string */
    public $parentStatusAttributeName = 'current_status_id';
    /** @var string */
    public $childStatusIdAttributeName = 'status_id';
    /** @var string */
    public $errorMsgEmptyStatus = 'You must fill "Status"';
    /** @var string */
    public $errorMsgWrongJumpStatus = 'Incorrect sequence of statuses';
    /** @var string */
    public $errorMsgPermission = 'You haven\'t permission';

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'prevalidateStatus',
        ];
    }

    /**
     * @param ActiveRecord $rowModel
     * @return array
     */
    public function getAvailableStatuses($rowModel = null)
    {
        $allStatuses = $rowModel::getAllStatus();

        //filter by jumps
        if (defined($this->owner::className() . '::ALLOWED_STATUSES')) {
            if ($rowModel && $allowedStatuses = $this->owner::ALLOWED_STATUSES[$rowModel->{$this->getParentMethodName}->{$this->parentStatusAttributeName}]) {
                $allStatuses = array_filter($allStatuses, function ($k) use ($allowedStatuses) {
                    return in_array($k, $allowedStatuses);
                }, ARRAY_FILTER_USE_KEY);
            }
        }

        //filter by permissions
        if ($user = \Yii::$app->user) {
            if (defined($this->owner::className() . '::STATUSES_BY_PERMISSIONS')) {
                $permAllowStatuses = [];
                foreach ($this->owner::STATUSES_BY_PERMISSIONS as $perm => $statuses) {
                    if ($user->can($perm)) {
                        $permAllowStatuses = array_merge($permAllowStatuses, $statuses);
                    }
                }
                $permAllowStatuses = array_filter($permAllowStatuses);
                $allStatuses = array_filter($allStatuses, function ($k) use ($permAllowStatuses) {
                    return in_array($k, $permAllowStatuses);
                }, ARRAY_FILTER_USE_KEY);
            }
        }

        if($this->sortListAsc){
            asort($allStatuses);
        }
        return $allStatuses;
    }

    public function prevalidateStatus($event)
    {
        if ($event->sender->hasErrors()) {
            return;
        }
        if (!$event->sender->{$this->childStatusIdAttributeName}) {
            $event->sender->addError($this->childStatusIdAttributeName, $this->errorMsgEmptyStatus);
        }

        //validate by jumps
        if (defined($this->owner::className() . '::ALLOWED_STATUSES')) {
            $model = $event->sender->{$this->getParentMethodName};
            if ($allowedStatuses = $this->owner::ALLOWED_STATUSES[$model->{$this->parentStatusAttributeName}]) {
                if (!in_array($event->sender->status_id, $allowedStatuses)) {
                    $this->owner->addError($this->childStatusIdAttributeName, $this->errorMsgWrongJumpStatus);
                }
            }
        }

        //validate by permissions
        if ($user = \Yii::$app->user) {
            if (defined($this->owner::className() . '::STATUSES_BY_PERMISSIONS')) {
                $permAllowStatuses = [];
                foreach ($this->owner::STATUSES_BY_PERMISSIONS as $perm => $statuses) {
                    if ($user->can($perm)) {
                        $permAllowStatuses = array_merge($permAllowStatuses, $statuses);
                    }
                }
                $permAllowStatuses = array_filter($permAllowStatuses);
                if (!in_array($event->sender->status_id, $permAllowStatuses)) {
                    $this->owner->addError($this->childStatusIdAttributeName, $this->errorMsgPermission);
                }
            }
        }
    }
}