<?php

/*
 * This file is part of Jitamin.
 *
 * Copyright (C) Jitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../Base.php';

use Jitamin\Action\TaskMoveColumnCategoryChange;
use Jitamin\Bus\Event\TaskEvent;
use Jitamin\Model\CategoryModel;
use Jitamin\Model\ProjectModel;
use Jitamin\Model\TaskFinderModel;
use Jitamin\Model\TaskModel;

class TaskMoveColumnCategoryChangeTest extends Base
{
    public function testSuccess()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);
        $categoryModel = new CategoryModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test1']));
        $this->assertEquals(2, $projectModel->create(['name' => 'test2']));
        $this->assertEquals(1, $categoryModel->create(['name' => 'c1', 'project_id' => 1]));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test']));

        $event = new TaskEvent([
            'task_id' => 1,
            'task'    => [
                'project_id'  => 1,
                'column_id'   => 1,
                'category_id' => 1,
                'position'    => 1,
                'swimlane_id' => 0,
            ],
        ]);

        $action = new TaskMoveColumnCategoryChange($this->container);
        $action->setProjectId(1);
        $action->setParam('category_id', 1);
        $action->setParam('dest_column_id', 2);

        $this->assertTrue($action->execute($event, TaskModel::EVENT_UPDATE));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);
        $this->assertEquals('test', $task['title']);
        $this->assertEquals(2, $task['column_id']);
    }

    public function testWithWrongColumn()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $categoryModel = new CategoryModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test1']));
        $this->assertEquals(2, $projectModel->create(['name' => 'test2']));
        $this->assertEquals(1, $categoryModel->create(['name' => 'c1', 'project_id' => 1]));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test']));

        $event = new TaskEvent([
            'task_id' => 1,
            'task'    => [
                'project_id'  => 1,
                'column_id'   => 2,
                'category_id' => 1,
            ],
        ]);

        $action = new TaskMoveColumnCategoryChange($this->container);
        $action->setProjectId(1);
        $action->setParam('category_id', 1);
        $action->setParam('dest_column_id', 2);

        $this->assertFalse($action->execute($event, TaskModel::EVENT_UPDATE));
    }

    public function testWithWrongCategory()
    {
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $categoryModel = new CategoryModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test1']));
        $this->assertEquals(2, $projectModel->create(['name' => 'test2']));
        $this->assertEquals(1, $categoryModel->create(['name' => 'c1', 'project_id' => 1]));
        $this->assertEquals(2, $categoryModel->create(['name' => 'c2', 'project_id' => 1]));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test']));

        $event = new TaskEvent([
            'task_id' => 1,
            'task'    => [
                'project_id'  => 1,
                'column_id'   => 1,
                'category_id' => 2,
            ],
        ]);

        $action = new TaskMoveColumnCategoryChange($this->container);
        $action->setProjectId(1);
        $action->setParam('category_id', 1);
        $action->setParam('dest_column_id', 2);

        $this->assertFalse($action->execute($event, TaskModel::EVENT_UPDATE));
    }
}
