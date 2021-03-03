@extends('includes.main')
@section('head_extra')
<link rel="stylesheet" href="{{ asset('back/dist/css/yoast.css') }}">
<link rel="stylesheet" href="{{ asset('back/assets/libs/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('back/assets/libs/cropper/dist/cropper.min.css') }}">
<style>
    .invalid-feedback {
    width: 100%;
    margin-top: .25rem;
    font-size: 95%;
    color: #f93a3a;
    }
</style>
@stop
@section('content')
<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
               
                <h4 class="page-title">@if($stored) Edit Post @else Create Post @endif</h4>
                
                <div class="d-flex align-items-center"></div>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex no-block justify-content-end align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="#">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Create Post</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title"> Post</h4>
                                <form class="m-t-30" method="post">
                                    @csrf
                                    <div class="form-group">
                                        <label for="title">Title</label>
                                        <input type="text" class="form-control {{-- {{ $errors->has('title') ? ' is-invalid' : '' }} --}}"
                                            id="title" aria-describedby="emailHelp" placeholder="Enter Title" name="title"
                                            value="{{ $stored->title ?? '' }}" required>
                                        {{-- @if ($errors->has('title'))
                                        <div class="invalid-feedback">{{ $errors->first('title') }}</div>
                                        @endif --}}
                                    </div>
                                    <div class="form-group">
                                        <label for="editor">Content</label>
                                        <div id="editor">{!!  $stored->description ?? old('description') !!}</div>
                                        <div id="counter"></div>
                                        @if ($errors->has('description'))
                                        <div class="invalid-feedback">{{ $errors->first('description') }}</div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <div class="col-3">
                                            <img id="image" data-src="#" class="img img-thumbnail" src="{{ $stored->pic ?? "" }}"/>
                                            <br/>
                                        </div>
                                        <label for="imgInp">Featured Image
                                        <input type="file" id="imgInp" accept="image/*" name=""
                                            class="hidden-field">
                                        </label>
                                        <div class="invalid-feedback image-error">Featured Image is required</div>
                                    </div>
                                    <hr>
                                    <h3>SEO</h3>
                                    <div class="form-group">
                                        <div id="snippet" class="output"></div>
                                        <label for="focusKeyword">Focus Keyword</label>
                                        <input id="focusKeyword" placeholder="Enter Your main keyword" name="focus"
                                        value="{{ $stored->meta_keyword ?? "" }}">
                                        <div id="output" class="output"></div>
                                    </div>
                                    
                                    <button type="button" class="btn btn-warning save"  data-status="0">draft</button>
                                    
                                    <button type="button" class="btn btn-primary save" data-status="3">Upload </button>
                                
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section("script_extra")
<script src="{{ asset('back/dist/js/editor.js') }}"></script>
<script src="{{ asset('back/assets/libs/select2/dist/js/select2.full.min.js') }}"></script>
<script src="{{ asset('back/assets/libs/cropper/dist/cropper.min.js') }}"></script>
<script>
    $(function () {
    
    
        /**
         * YOAST SEO STARTS
         */
        var focusKeywordField = document.getElementById("focusKeyword");
        var contentField = document.getElementById("editor");
    
    
        var snippetPreview = new YoastSnippetPreview({
            targetElement: document.getElementById("snippet"),
            baseURL: "{{url('/')}}/article/",
            placeholder: {
                urlPath: "Slug goes here"
            },
            data: {
                title: "{{ $stored->meta_title ?? '' }}",
                metaDesc: "{{ $stored->meta_description ?? "" }}",
                urlPath: "{{ substr(implode(" ",explode('-',$stored->slug ?? "")),0,-6) ?? "" }}"
            }
        });
    
    
        var app = new YoastApp({
            snippetPreview: snippetPreview,
            targets: {
                output: "output"
            },
    
            callbacks: {
                getData: function () {
                    return {
                        keyword: focusKeywordField.value,
                        text: contentField.innerText
                    };
                }
            }
        });
    
        app.refresh();
    
        focusKeywordField.addEventListener('change', app.refresh.bind(app));
        contentField.addEventListener('change', app.refresh.bind(app));
    
        /**
         * YOAST SEO ENDS
         */
    
        /*
         * Editor Started
         */
    
        var editorinstance;
        ClassicEditor.create(document.querySelector('#editor'), {
            ckfinder: {
                uploadUrl: '{{ route("article-image-upload") }}?_token={{ csrf_token() }}'
            },
            mediaEmbed: {
                // configuration...
            }
        }).then(editor => {
            editorinstance = editor;
    
            editor.model.document.on('change', () => {
    
                document.querySelector('#counter').innerText = 'Length of the text in the document: ' + countCharacters(editor.model.document);
    
    
                var editorData = editorinstance.getData();
    
                $("#editor").html($(editorData).text());
                $("#editor").trigger("change");
    
            });
            //    editor.model.document.on( 'keyup', () => {
            //          document.querySelector('#counter').innerText = 'Length of the text in the document: ' + countCharacters( editor.model.document );
            //              } );
            // Update the counter when editor is ready.
            document.querySelector('#counter').innerText = 'Length of the text in the document: ' + countCharacters(editor.model.document);
    
            //editor.isReadOnly = true;
            //   var dataaaa = editor.getData();
            //  console.log(dataaaa)
    
    
        })
            .catch(error => {
                console.error(error);
            });
    
    
        function countCharacters(document) {
    
    
            var editorData = editorinstance.getData();
            const rootElement = $(editorData).text();
    
    
            return countCharactersInElement(rootElement);
    
            // Returns length of the text in specified `node`
            //
            // @param {module:engine/model/node~Node} node
            // @returns {Number}
            function countCharactersInElement(node) {
                let chars = 50000;
    
    
                chars -= rootElement.length;
                wordcount = rootElement.length;
                // var max_characters =2000;
                // var remaining = max_characters - chars;
                // chars = remaining;
                // charsw = child.data;
                // console.log(charsw);
                if (wordcount >= 50000) {
                    // editor.isReadOnly = true;
                    // charssss = charsw.substr(0, 5);
                    // console.log(charssss);
                    // swal({
                    //     type: 'error',
                    //     title: 'Error!',
                    //     text: 'Your Character limit is reached'
                    // })
                } else {
                    //  console.log('no');
                }
    
                if (wordcount >= 5) {
                    $("#draft").show();
                } else if (wordcount <= 5) {
                    $("#draft").hide();
                }
    
    
                return chars;
            }
        }
    
        /*
         * Editor Ended
         */
    
        function formatRepoSelection(repo) {
            return repo.label || repo.text;
        }
    
        function formatRepo(repo) {
            if (repo.loading) return repo.text;
            var markup = repo.label;
            return markup;
        }
    
       
    
        /*
         * Cropper Start
         */
    
        var console = window.console || {
            log: function () {
            }
        };
        var URL = window.URL || window.webkitURL;
        var $image = $('#image');
        var $download = $('#download');
        var $dataX = $('#dataX');
        /*console.log($dataX);
        return;*/
        var $dataY = $('#dataY');
        var $dataHeight = $('#dataHeight');
        var $dataWidth = $('#dataWidth');
        var $dataRotate = $('#dataRotate');
        var $dataScaleX = $('#dataScaleX');
        var $dataScaleY = $('#dataScaleY');
        var options = {
            aspectRatio: 16 / 9,
            viewMode: 1,
            guides: false,
            zoomable: false,
            highlight: false,
            autoCrop: false,
            cropBoxResizable: false,
            minCropBoxWidth: 1400,
            minCropBoxHeight: 495,
            preview: '.img-preview',
            ready() {
                this.cropper.crop();
            },
            /*crop: function (e) {
                $dataX.val(Math.round(e.detail.x));
                $dataY.val(Math.round(e.detail.y));
                $dataHeight.val(Math.round(e.detail.height));
                $dataWidth.val(Math.round(e.detail.width));
                $dataRotate.val(e.detail.rotate);
                $dataScaleX.val(e.detail.scaleX);
                $dataScaleY.val(e.detail.scaleY);
            }*/
        };
    
        /*console.log(options);
        return;*/
        var originalImageURL = $image.attr('src');
        var uploadedImageName = 'cropped.jpg';
        var uploadedImageType = 'image/jpeg';
        var uploadedImageURL;
    
    
        // Tooltip
        $('[data-toggle="tooltip"]').tooltip();
    
    
        // Cropper
        $image.on({
            ready: function (e) {
                console.log(e.type);
            },
            cropstart: function (e) {
                console.log(e.type, e.detail.action);
            },
            cropmove: function (e) {
                console.log(e.type, e.detail.action);
            },
            cropend: function (e) {
                console.log(e.type, e.detail.action);
            },
            crop: function (e) {
                console.log(e.type);
            },
            zoom: function (e) {
                console.log(e.type, e.detail.ratio);
            }
        }).cropper(options);
    
    
        // Buttons
        if (!$.isFunction(document.createElement('canvas').getContext)) {
            $('button[data-method="getCroppedCanvas"]').prop('disabled', true);
        }
    
        if (typeof document.createElement('cropper').style.transition === 'undefined') {
            $('button[data-method="rotate"]').prop('disabled', true);
            $('button[data-method="scale"]').prop('disabled', true);
        }
    
    
        // Download
        /*if (typeof $download[0].download === 'undefined') {
            $download.addClass('disabled');
        }*/
    
    
        // Options
        $('.docs-toggles').on('change', 'input', function () {
            var $this = $(this);
            var name = $this.attr('name');
            var type = $this.prop('type');
            var cropBoxData;
            var canvasData;
    
            if (!$image.data('cropper')) {
                return;
            }
    
            if (type === 'checkbox') {
                options[name] = $this.prop('checked');
                cropBoxData = $image.cropper('getCropBoxData');
                canvasData = $image.cropper('getCanvasData');
    
                options.ready = function () {
                    $image.cropper('setCropBoxData', cropBoxData);
                    $image.cropper('setCanvasData', canvasData);
                };
            } else if (type === 'radio') {
                options[name] = $this.val();
            }
    
            $image.cropper('destroy').cropper(options);
        });
    
    
        // Methods
        $('.docs-buttons').on('click', '[data-method]', function () {
            var $this = $(this);
            var data = $this.data();
            var cropper = $image.data('cropper');
            var cropped;
            var $target;
            var result;
    
            if ($this.prop('disabled') || $this.hasClass('disabled')) {
                return;
            }
    
            if (cropper && data.method) {
                data = $.extend({}, data); // Clone a new one
    
                if (typeof data.target !== 'undefined') {
                    $target = $(data.target);
    
                    if (typeof data.option === 'undefined') {
                        try {
                            data.option = JSON.parse($target.val());
                        } catch (e) {
                            console.log(e.message);
                        }
                    }
                }
    
                cropped = cropper.cropped;
    
                switch (data.method) {
                    case 'rotate':
                        if (cropped && options.viewMode > 0) {
                            $image.cropper('clear');
                        }
    
                        break;
    
                    case 'getCroppedCanvas':
                        if (uploadedImageType === 'image/jpeg') {
                            if (!data.option) {
                                data.option = {};
                            }
    
                            data.option.fillColor = '#fff';
                        }
    
                        break;
                }
    
                result = $image.cropper(data.method, data.option, data.secondOption);
    
                switch (data.method) {
                    case 'rotate':
                        if (cropped && options.viewMode > 0) {
                            $image.cropper('crop');
                        }
    
                        break;
    
                    case 'scaleX':
                    case 'scaleY':
                        $(this).data('option', -data.option);
                        break;
    
                    case 'getCroppedCanvas':
                        if (result) {
                            // Bootstrap's Modal
                            $('#getCroppedCanvasModal').modal().find('.modal-body').html(result);
    
                            if (!$download.hasClass('disabled')) {
                                download.download = uploadedImageName;
                                $download.attr('href', result.toDataURL(uploadedImageType));
                            }
                        }
    
                        break;
    
                    case 'destroy':
                        if (uploadedImageURL) {
                            URL.revokeObjectURL(uploadedImageURL);
                            uploadedImageURL = '';
                            $image.attr('src', originalImageURL);
                        }
    
                        break;
                }
    
                if ($.isPlainObject(result) && $target) {
                    try {
                        $target.val(JSON.stringify(result));
                    } catch (e) {
                        // console.log(e.message);
                    }
                }
    
            }
        });
    
    
        // Keyboard
        $(document.body).on('keydown', function (e) {
    
            if (!$image.data('cropper') || this.scrollTop > 300) {
                return;
            }
    
            switch (e.which) {
                case 37:
                    e.preventDefault();
                    $image.cropper('move', -1, 0);
                    break;
    
                case 38:
                    e.preventDefault();
                    $image.cropper('move', 0, -1);
                    break;
    
                case 39:
                    e.preventDefault();
                    $image.cropper('move', 1, 0);
                    break;
    
                case 40:
                    e.preventDefault();
                    $image.cropper('move', 0, 1);
                    break;
            }
    
        });
    
    
        // Import image
        var $inputImage = $('#imgInp');
    
        if (URL) {
            $inputImage.change(function () {
                var files = this.files;
                var file;
    
                if (!$image.data('cropper')) {
                    return;
                }
    
                if (files && files.length) {
                    file = files[0];
    
                    if (/^image\/\w+$/.test(file.type)) {
                        uploadedImageName = file.name;
                        uploadedImageType = file.type;
    
                        if (uploadedImageURL) {
                            URL.revokeObjectURL(uploadedImageURL);
                        }
    
                        uploadedImageURL = URL.createObjectURL(file);
                        $image.cropper('destroy').attr('src', uploadedImageURL).cropper(options);
                        // $inputImage.val('');
                    } else {
                        window.alert('Please choose an image file.');
                    }
                }
            });
        } else {
            $inputImage.prop('disabled', true).parent().addClass('disabled');
        }
    
        /*
         * Cropper Ends
         */
    
        $(document).on("click", ".save", function (e) {
    
                // console.log($("#imgInp").val());
                // return;
        
            e.preventDefault();
            var imgInpCnt = $('#imgInp')[0].files.length;
            var featuredImg = $('#image').attr("src");
            // alert(featuredImg);
            // return;
            if(featuredImg != "" || imgInpCnt != 0)
            {
                $(".image-error").hide();
            }
            else
            {
                $(".image-error").show();
                $('html, body').animate({
                    scrollTop: $("#tags").offset().top
                }, 500);
                return;
            }
    
                var $status = $(this).attr("data-status");
                var cropper = $image.data('cropper');
    
                /*console.log(cropper);
                return*/
    
                // console.log($('#imgInp')[0].files.length);
                // return;
                var $imageData = cropper.getData();
                // console.log($imageData);
                // return;
                var $imageNatural = cropper.getImageData();
    
                // console.log($imageNatural);
    
                var $title = $("input[name='title']").val().trim();
                var $article = editorinstance.getData();
                var $fileData = $("#imgInp").prop("files")[0];
                var $focusKeywordFieldvalue = $("#focusKeyword").val().trim();
                var $seotitle = $("#snippet-editor-title").val().trim();
                var $seoslug = $("#snippet-editor-slug").val().trim();
                var $meta_description = $("#snippet-editor-meta-description").val().trim();
    
                if($title == "")
                {
                    var errorSection = '<div class="invalid-feedback title-error">Title is required</div>';
                    if(!$(".title-error").is(":visible"))
                        $( errorSection ).insertAfter("#title");
    
                    $(".title-error").show();
                    $('html, body').animate({
                        scrollTop: $(".page-wrapper").offset().top
                    }, 500);
                    return;
                }
                else
                {
                    $(".title-error").remove();
                }
    
                if($seotitle == "")
                {
                    var errorSection = '<div class="invalid-feedback seo-title-error">SEO title is required</div>';
                    if(!$(".seo-title-error").is(":visible"))
                        $( errorSection ).insertAfter(".snippet-editor__progress-title");
    
                    $(".seo-title-error").show();
                    $('html, body').animate({
                        scrollTop: $(".snippet-editor__heading").offset().top
                    }, 200);
                    return;
                }
                else
                {
                    $(".seo-title-error").remove();
                }
    
                if($seoslug == "")
                {
                    var errorSection2 = '<div class="invalid-feedback seo-slug-error">SEO Slug is required</div>';
                    if(!$(".seo-slug-error").is(":visible"))
                        $( errorSection2 ).insertAfter(".snippet-editor__slug");
    
                    $(".seo-slug-error").show();
    
                    $('html, body').animate({
                        scrollTop: $(".snippet-editor__heading").offset().top
                    }, 200);
                    return;
                }
                else
                {
                    $(".seo-slug-error").remove();
                }
    
                if($meta_description == "")
                {
                    var errorSection3 = '<div class="invalid-feedback seo-description-error">Meta Description is required</div>';
                    if(!$(".seo-description-error").is(":visible"))
                        $( errorSection3 ).insertAfter(".snippet-editor__progress-meta-description");
    
                    $(".seo-description-error").show();
                    $('html, body').animate({
                        scrollTop: $("#snippet-editor-title").offset().top
                    }, 200);
                    return;
                }
                else
                {
                    $(".seo-description-error").remove();
                }
    
                if($focusKeywordFieldvalue == "")
                {
                    var errorSection3 = '<div class="invalid-feedback seo-focus-keyword-error">Meta Keyword is required</div>';
                    if(!$(".seo-focus-keyword-error").is(":visible"))
                        $( errorSection3 ).insertAfter("#focusKeyword");
    
                    $(".seo-focus-keyword-error").show();
                    $('html, body').animate({
                        scrollTop: $("#snippet-editor-title").offset().top
                    }, 200);
                    return;
                }
                else
                {
                    $(".seo-focus-keyword-error").remove();
                }
    
                // return;
                var formData = new FormData();
    
                formData.append("title", $title);
                formData.append("editor", $article);
                formData.append("img", $fileData);
                formData.append("width", $imageData.width);
                formData.append("height", $imageData.height);
                formData.append("ow", $imageNatural.naturalWidth);
                formData.append("oh", $imageNatural.naturalHeight);
                formData.append("x", $imageData.x);
                formData.append("y", $imageData.y);
                formData.append("rotate", $imageData.rotate);
                formData.append("status", $status);
                formData.append("meta_keyword", $focusKeywordFieldvalue);
                formData.append("meta_title", $seotitle);
                formData.append("slug", $seoslug);
                formData.append("postID", {{ $id }});
                formData.append("meta_description", $meta_description);
                formData.append("_token", "{{ csrf_token() }}");
    
                $.ajax({
                    url: "{{ route("submit") }}",
                    type: "post",
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (response) {

                        if(response == "Post has been updated successfully.")
                        {
                            var url = '{!! route('published-blog') !!}';
                            window.location.href = url;
                        }
                        else{

                            var url = '{!! route('draft-blog') !!}';
                            window.location.href = url;
                        } 
      
                    }
                });
    
    
        });
    
    
    });
</script>
@endsection
