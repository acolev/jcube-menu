<x-admin.layout :page-title="$pageTitle">
    <div class="row justify-content-center gy-3">
        <div class="col-lg-4 categories-tree">
            <div class="card b-radius--10">
                <div class="card-body">
                    <button class="btn btn--dark btn--shadow-default close-tree mx-3 mb-3" type="button" value="1">
                        {{ __('Collapse All') }}
                    </button>
                    <div id=jsTree></div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card b-radius--10 ">
                <div class="card-body">
                    <form action="{{ route('admin.menu.store', 0) }}" method="POST" enctype="multipart/form-data"
                          id="addForm">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="parent_id">
                            <div class="form-group row">
                                <div class="col">
                                    <x-form.input name="name" label="Name" :value="old('name')"/>
                                </div>
                                <div class="col-md-4">
                                    <x-form.input name="icon" label="Icon" :value="old('icon')"/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col">
                                    <x-form.input type="select" name="object_type" label="Type" :value="old('type')"
                                                  :variants="config('menu.types')"/>
                                </div>
                                <div class="col-md-4">
                                    <x-form.input name="object_id" label="ID" :value="old('id')"/>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-block btn--success mr-2">@lang('Save')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('breadcrumb-plugins')
        @if(request()->routeIs('admin.menu.all'))
            <button class="btn btn-sm btn--primary add-parent mb-xl-0 mb-2 box--shadow1 text--small"><i
                    class="las la-plus"></i> @lang('Add Root')</button>
            <button class="btn btn-sm btn--success add-chlid mb-xl-0 mb-2 box--shadow1 text--small" disabled><i
                    class="las la-plus"></i> @lang('Add Child')</button>
        @endif
        @isset($languages)
            <x-select-edit-lang/>
        @endisset
    @endpush

    @push('script-lib')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>
    @endpush

    @push('style-lib')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css">
        <style>
            .jstree-icon {
                font-size: 20px !important;
                color: var(--primary);
            }
        </style>
    @endpush

    @push('script')
        <script>
            const treeId = '#jsTree'
            const options = {
                core: {
                    data: {
                        url: '{{ route('admin.menu.list') }}',
                    },
                    theme: {variant: "large"},
                    "check_callback": true
                },
                plugins: ['dnd', 'unique'],
            };
            let selected = null;

            const updateTree = (init = false) => {
                if (!init) {
                    $.jstree.reference(treeId).destroy()
                }
                $.jstree.create(treeId, options)

                $(treeId).on('move_node.jstree ', function (e, {parent, node, position}) {
                    const url = `{{ route('admin.menu.move') }}`
                    const response = fetch(url, {
                        method: "POST",
                        mode: "cors",
                        cache: "no-cache",
                        credentials: "same-origin",
                        headers: {
                            "Content-Type": "application/json",
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        redirect: "follow",
                        referrerPolicy: "no-referrer",
                        body: JSON.stringify({id: node.id, parent, position})
                    });
                    response.then(data => data.json()).then(data => {
                        // console.log(data)
                        return true;
                    })
                });
                $(treeId).on('changed.jstree', function (e, {node}) {
                    const form = $('#addForm');
                    const data = node.data;
                    selected = data.id;
                    const action = `{{ route('admin.menu.store', '') }}/${data.id}`

                    form.attr('action', action);
                    form.find('.select2-auto-tokenize').text('');
                    form.find('input[name=parent_id]').val(data.parent_id);
                    form.find('input[name=name]').val(data.name);
                    form.find('input[name=icon]').val(data.icon);
                    form.find('input[name=object_id]').val(data.object_id);

                    const objId = form.find('[name=object_type]');

                    for (opt of objId.find('option')) {
                        opt.removeAttribute('selected');
                        if (opt.value === data.object_type)
                            opt.setAttribute('selected', true);
                    }


                    $('.add-chlid').removeAttr('disabled');
                });
            }

            (function () {
                updateTree(true)
            })();

            $(document).on('click', '.close-tree', function () {
                const treeElement = $.jstree.reference(treeId);
                var val = +this.value;
                if (val === 1) {
                    $(this).text("@lang('Expand All')")
                    $(this).val(2);
                    treeElement.close_all();
                } else {
                    $(this).text("@lang('Collapse All')")
                    $(this).val(1);
                    treeElement.open_all();
                }
            });

            $(document).on('click', '.add-chlid', function () {
                var form = $('#addForm');
                var parent_id = selected;
                var action = `{{ route('admin.menu.store', 0) }}`
                form.attr('action', action);
                form.find("input[type=text], textarea, select").val("");
                form.find('input[name=parent_id]').val(parent_id);
            });

            $(document).on('click', '.add-parent', function () {
                $('.add-chlid').attr('disabled', 'disabled');
                var form = $('#addForm');
                var action = `{{ route('admin.menu.store', 0) }}`;

                var parent = $(document).find('.parent.active');

                var length = parent.length;

                if (length > 0) {
                    parent.first().removeClass('active');
                    parent.parents('li').find('.delete-btn').addClass('d-none');
                }

                form.attr('action', action);
                form.find("input[type=text], textarea, select").val("");
                form.find('input[name=parent_id]').val('');
            });
        </script>
    @endpush
</x-admin.layout>
