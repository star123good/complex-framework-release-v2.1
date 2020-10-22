<?php

    namespace Library;

    if ( ! defined('CORRECT_PATH')) exit();

/**********************************************************************************************
 *
 *      Rest Controller Class
 *
 *      RESTful API - index(GET), show(GET), new(GET), edit(GET), insert(POST), update(POST,PUT), remove(POST,DELETE)
 * 
 * Action	Expected API request
 * Get list	GET http://my.api.url/posts?sort=["title","ASC"]&range=[0, 24]&filter={"title":"bar"}
 * Get one record	GET http://my.api.url/posts/123
 * Get several records	GET http://my.api.url/posts?filter={"id":[123,456,789]}
 * Get related records	GET http://my.api.url/posts?filter={"author_id":345}
 * Create a record	POST http://my.api.url/posts/123
 * Update a record	PUT http://my.api.url/posts/123
 * Update records	PUT http://my.api.url/posts?filter={"id":[123,124,125]}
 * Delete a record	DELETE http://my.api.url/posts/123
 * Delete records	DELETE http://my.api.url/posts?filter={"id":[123,124,125]}
 *
**********************************************************************************************/


use Library\Log as Log;
use Library\Controller as Controller;


/*
 *      Rest Controller Class
 */
class RestController extends Controller {

    protected   $model,                         // Model instance
                $path;                          // view path


    public function __construct($model=null)
    {
        parent::__construct();

        $str = static::class;
        $this->path = str_ireplace("Controller", "", substr($str, strrpos($str, '\\') + 1));

        // set model
        if (is_null($model)) $model = "Models\\" . $this->path . "Model";
        $this->_setModel($model);

        // set response default type as JSON
        $this->_setJSON(true);
    }

    /**
     *      set model
     *      @return void
     */
    protected function _setModel($model)
    {
        $this->model = $model;
    }

    /**
     *      set response
     *      @param  mixed   $data
     *      @param  string  $page
     *      @param  bool    $error
     *      @return void
     */
    protected function _setResponse($data, $page=null, $error=false)
    {
        if ($error) {
            // not content error
            $this->_getResponse()
                ->setStatusCode(500)
                ->setContent($data);
        }
        else {
            if ($this->flagJSON) {
                // json
                $this->_getResponse()
                    ->setContent($data);
            }
            else {
                // html
                $this->_getView()
                    ->setData('data', $data)
                    ->setPage($this->path . '/' . $page);
            }
        }
    }

    /**
     *      index
     *      show list
     *      @method GET     /model
     *      @return void
     *      response JSON {"status":"success", "count": count, "next": next, "previous": next, "result":[results]}
     *      response HTML show.view.php
     */
    public function index()
    {
        $data = $this->model::where('1')->getAll();

        $this->_setResponse($data, "index", empty($data));
    }

    /**
     *      show
     *      show item
     *      @method GET     /model/{id}
     *      @return void
     *      response JSON {"status":"success", "result":result}
     *      response HTML show.view.php
     */
    public function show($id)
    {
        $data = $this->model::find($id);

        $this->_setResponse($data, "show", !$data->isExist());
    }

    /**
     *      new
     *      show new created item
     *      @method GET     /model/new
     *      @return void
     *      response HTML new.view.php
     */
    public function new()
    {
        $this->_setJSON(false);

        $this->_setResponse(null, "new");
    }

    /**
     *      edit
     *      show edited item
     *      @method GET     /model/edit/{id}
     *      @return void
     *      response HTML edit.view.php
     */
    public function edit($id)
    {
        $this->_setJSON(false);

        $data = $this->model::find($id);

        $this->_setResponse($data, "edit", !$data->isExist());
    }

    /**
     *      insert
     *      add new item
     *      @method POST    /model
     *      @return void
     *      response JSON {"status":"success", "message":"Success to insert new item."}
     */
    public function insert()
    {
        $this->_setJSON(true);

        $result = $this->model::create($this->_getRequest()->getParameters())->save();

        $this->_setResponse($result);
    }

    /**
     *      update
     *      update item     /model/{id}
     *      @method PUT
     *      @return void
     *      response JSON {"status":"success", "id":id, "message":"Success to update the item."}
     */
    public function update($id)
    {
        $this->_setJSON(true);

        $data = $this->model::find($id);

        if ($data->isExist()) {
            $result = $data->update($this->_getRequest()->getParameters())->save();
            $flag = false;
        }
        else {
            $result = null;
            $flag = true;
        }

        $this->_setResponse($result, null, $flag);
    }

    /**
     *      remove
     *      remove item
     *      @method DELETE  /model/{id}
     *      @return void
     *      response JSON {"status":"success", "id":id, "message":"Success to remove a item."}
     */
    public function remove($id)
    {
        $this->_setJSON(true);

        $data = $this->model::find($id);

        if ($data->isExist()) {
            $result = $data->delete();
            $flag = false;
        }
        else {
            $result = null;
            $flag = true;
        }

        $this->_setResponse($result, null, $flag);
    }
    
}