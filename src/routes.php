<?php
// use Illuminate\Support\Facades\Route;

	Route::post("article-image-upload","Edgewizz\Blog\BlogController@articleImageUpload")->name('article-image-upload');
	Route::any("add-blog","Edgewizz\Blog\BlogController@create")->name('add-blog');
	Route::any("edit-blog/{id}","Edgewizz\Blog\BlogController@create")->name('edit-blog');
	
	Route::any("submit","Edgewizz\Blog\BlogController@submit")->name('submit');

	Route::any("view-blog","Edgewizz\Blog\BlogController@publishedPosts")->name('published-blog');
	Route::any("draft-blog","Edgewizz\Blog\BlogController@draftPosts")->name('draft-blog');

	Route::get('hello', function(){ 
	dd('hi'); });