<?php


namespace humhub\modules\tasks\models\lists;


use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\tasks\models\Sortable;
use yii\base\Object;

class TaskListRoot extends Object implements Sortable
{
    /**
     * @var ContentContainerActiveRecord
     */
    public $contentContainer;

    public function moveItemIndex($tskListId, $newIndex)
    {
        $testId = null;
        $transaction = TaskList::getDb()->beginTransaction();
        try {
            $taskList = TaskList::findOne(['id' => $tskListId]);

            if($taskList->addition->sort_order === $newIndex) {
                return;
            }

            $taskLists = TaskList::findOverviewLists($this->contentContainer)->andWhere(['!=', 'content_tag.id', $taskList->id])->all();

            // make sure no invalid index is given
            if($newIndex < 0) {
                $newIndex = 0;
            } else if($newIndex >= count($taskLists)) {
                $newIndex = count($taskLists) -1;
            }


            array_splice($taskLists, $newIndex, 0, [$taskList]);


            foreach ($taskLists as $index => $item) {
                $item->addition->updateAttributes(['sort_order' => $index]);
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}