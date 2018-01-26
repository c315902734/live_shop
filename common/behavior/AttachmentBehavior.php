<?php

/**
*  Author:Administrator
*  Created time :  2017\10\11 0011 14:49
*  File Name : Attachment.php
*  Description :
*/
namespace common\behavior;

use yii\db\ActiveRecord;
use yii\base\Behavior;
use yii\web\UploadedFile;
/**
 * Class Attachment
 * @package common\behavior
 */

Class AttachmentBehavior extends Behavior{
    
    //public $uploadFiles;
    //public $uploadedFiles;
    private $files;
    //private $admin_id;
    //文件路径
    public $path= '@backend/upload/';
    
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            ActiveRecord::EVENT_AFTER_INSERT    => 'afterSave',
            //ActiveRecord::EVENT_AFTER_UPDATE    => 'afterSave',
            ActiveRecord::EVENT_BEFORE_DELETE   => 'beforeDelete',
        ];
    }
    
    public function beforeValidate(){
        $this->files = UploadedFile::getInstances($this->owner, $this->uploadFiles);
    }
    
    
    /**
     * 返回拥有者的唯一Id
     * @return string
     */
    public function getOwnerId()
    {
        return $this->owner->id;
    }
    
    /**
     * 在主模型保存后挨个保存附件
     */
    public function afterSave()
    {
        
        foreach ($this->files as $file) {
            $model = new AdStartupImages();
            $model->title = $file->name;
            $model->file_url = date('Ymd') .'-'.time() .$file->name. '-' . $file->extension;
            $model->ad_startup_id = $this->owner->id;
            $model->create_time= time();
            $model->update_time = time();
            $model->save();
        }
        
    }
    
    /**
     * 在主模型删除之前删除所有附件
     * @return bool
     */
    public function beforeDelete()
    {
        
        foreach ($this->owner->{$this->uploadedFiles} as $file) {
            $file->delete();
        }
        return true;
    }

}