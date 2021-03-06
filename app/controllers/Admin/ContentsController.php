<?php
namespace Admin;

use \Content;
use \Input;
use \Redirect;
use \Validator;
use \View;

class ContentsController extends BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return View::make('admin.contents.index')
            ->with('contents', Content::alphabetically()->get());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return View::make('admin.contents.create')
            ->with('content', new Content);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $rules = array(
            'name'    => 'required',
            'slug'    => 'required|unique:contents',
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->passes()) {
            $content = new Content;
            $content->fill(Input::all());
            $content->save();

            return Redirect::route('admin.contents.index')
                ->with('success', 'Content has been successfully created');
        } else {
            return Redirect::route('admin.contents.create')
                ->withInput()
                ->withErrors($validator);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        return View::make('admin.contents.edit')
            ->with('content', Content::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $content = Content::findOrFail($id);

        $rules = array(
            'name'    => 'required',
            'slug'    => "required|unique:contents,slug,{$content->id}",
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->passes()) {
            $content->fill(Input::all());
            $content->save();

            return Redirect::route('admin.contents.index')
                ->with('success', 'Content has been successfully updated');
        } else {
            return Redirect::route('admin.contents.edit', $content->id)
                ->withInput()
                ->withErrors($validator);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $content = Content::findOrFail($id);

        if ($content->is_deletable) {
            $content->delete();

            return Redirect::route('admin.contents.index')
                ->with('success', 'Content has been successfully deleted');
        } else {
            return Redirect::route('admin.contents.index')
                ->with('danger', 'This content cannot be deleted');
        }
    }

}
