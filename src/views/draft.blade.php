
<div class="page-wrapper">
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Draft Blogs</h4>
                <div class="d-flex align-items-center"></div>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex no-block justify-content-end align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="#">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Draft Blogs</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <!-- ============================================================== -->
        <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">{{-- <div class="float-right"><a href="{{ route('add-blog') }}" class="btn btn-success">Add</a></div> </h4> --}}
                    <h6 class="card-subtitle">List of all the Draft Blogs</h6>
                    <div class="table-responsive">
                        <table id="zero_config" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Type</th>
                                    <th>Submitted At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
            
</div>

    <script src="{{ asset('back/assets/extra-libs/DataTables/datatables.min.js') }}"></script>
    <script src="{{ asset('back/assets/libs/sweetalert2/dist/sweetalert2.min.js') }}"></script>

    <script type="text/javascript">

        $(function () {

            var $table = $('#zero_config').DataTable({
                "ordering": false,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "type": "post",
                    "data": function (d) {
                        d.ajax = 1;
                        d._token = "{{ csrf_token() }}";
                    }
                },
                columns: [
                    {data: 'title', name: 'title', searchable: true},
                    {data: 'author', name: 'author', searchable: true},
                    {data: 'type', name: 'type', searchable: true},
                    {data: 'date', name: 'date', searchable: false},
                    {data: 'details', name: 'details',  orderable: false, searchable: false }
                ]
            });


        });


    </script>











