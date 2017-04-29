@extends('voyager::master')

@section('page_title','All '.$dataType->display_name_plural)

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i> {{ $dataType->display_name_plural }}
        <a href="{{ route('voyager.'.$dataType->slug.'.create') }}" class="btn btn-success">
            <i class="voyager-plus"></i> {{trans('voyager.Add New')}}
        </a>
    </h1>
@stop

@section('content')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <table id="dataTable" class="table table-hover">
                            <thead>
                                <tr>
                                    @foreach($dataType->browseRows as $rows)
                                    <th>{{ $rows->display_name }}</th>
                                    @endforeach
                                    <th class="actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dataTypeContent as $data)
                                <tr>
                                    @foreach($dataType->browseRows as $row)
                                        <td>
                                            <?php $options = json_decode($row->details); ?>
                                            @if($row->type == 'image')
                                                <img src="@if( strpos($data->{$row->field}, 'http://') === false && strpos($data->{$row->field}, 'https://') === false){{ Voyager::image( $data->{$row->field} ) }}@else{{ $data->{$row->field} }}@endif" style="width:100px">
                                            @elseif($row->type == 'select_multiple')
                                                @if(property_exists($options, 'relationship'))

                                                    @foreach($data->{$row->field} as $item)
                                                        @if($item->{$row->field . '_page_slug'})
                                                        <a href="{{ $item->{$row->field . '_page_slug'} }}">{{ $item->{$row->field}  }}</a>@if(!$loop->last), @endif
                                                        @else
                                                        {{ $item->{$row->field}  }}
                                                        @endif
                                                    @endforeach

                                                    {{-- $data->{$row->field}->implode($options->relationship->label, ', ') --}}
                                                @elseif(property_exists($options, 'options'))
                                                    @foreach($data->{$row->field} as $item)
                                                     {{ $options->options->{$item} . (!$loop->last ? ', ' : '') }}
                                                    @endforeach
                                                @endif
                                                @if ($data->{$row->field} && isset($options->relationship))
                                                    {{ $data->{$row->field}->implode($options->relationship->label, ', ') }}
                                                @endif
                                            @elseif($row->type == 'select_dropdown' && property_exists($options, 'options'))

                                                @if($data->{$row->field . '_page_slug'})
                                                    <a href="{{ $data->{$row->field . '_page_slug'} }}">@{{ $options->options->{$data->{$row->field}} }}</a>
                                                @else
                                                    @{{ $options->options->{$data->{$row->field}} }}
                                                @endif
    

                                            @elseif($row->type == 'select_dropdown' && $data->{$row->field . '_page_slug'})
                                                <a href="{{ $data->{$row->field . '_page_slug'} }}">{{ $data->{$row->field}  }}</a>
                                            @elseif($row->type == 'date')
                                            {{ $options && property_exists($options, 'format') ? \Carbon\Carbon::parse($data->{$row->field})->formatLocalized($options->format) : $dataTypeContent->{$row->field} }}
                                            @elseif($row->type == 'checkbox')
                                                @if($options && property_exists($options, 'on') && property_exists($options, 'off'))
                                                    @if($data->{$row->field})
                                                    <span class="label label-info">{{ $options->on }}</span>
                                                    @else
                                                    <span class="label label-primary">{{ $options->off }}</span>
                                                    @endif
                                                @else
                                                {{ $data->{$row->field} }}
                                                @endif
                                            @elseif($row->type == 'text')
                                            <div class="readmore">{{ $data->{$row->field} }}</div>
                                            @elseif($row->type == 'text_area')
                                            <div class="readmore">{{ $data->{$row->field} }}</div>                                            
                                            @elseif($row->type == 'rich_text_box')
                                            <div class="readmore">{{ $data->{$row->field} }}</div>
                                            @else
                                                {{ $data->{$row->field} }}
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="no-sort no-click">
                                        <div class="btn-sm btn-danger pull-right delete" data-id="{{ $data->id }}" id="delete-{{ $data->id }}">
                                            <i class="voyager-trash"></i> {{trans('voyager.Delete')}}
                                        </div>
                                        <a href="{{ route('voyager.'.$dataType->slug.'.edit', $data->id) }}" class="btn-sm btn-primary pull-right edit">
                                            <i class="voyager-edit"></i> {{trans('voyager.Edit')}}
                                        </a>
                                        <a href="{{ route('voyager.'.$dataType->slug.'.show', $data->id) }}" class="btn-sm btn-warning pull-right">
                                            <i class="voyager-eye"></i> {{trans('voyager.View')}}
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if (isset($dataType->server_side) && $dataType->server_side)
                            <div class="pull-left">
                                <div role="status" class="show-res" aria-live="polite">Showing {{ $dataTypeContent->firstItem() }} to {{ $dataTypeContent->lastItem() }} of {{ $dataTypeContent->total() }} entries</div>
                            </div>
                            <div class="pull-right">
                                {{ $dataTypeContent->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-danger fade" tabindex="-1" id="delete_modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="voyager-trash"></i> Are you sure you want to delete
                        this {{ $dataType->display_name_singular }}?</h4>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('voyager.'.$dataType->slug.'.index') }}" id="delete_form" method="POST">
                        {{ method_field("DELETE") }}
                        {{ csrf_field() }}
                        <input type="submit" class="btn btn-danger pull-right delete-confirm"
                               value="Yes, Delete This {{ $dataType->display_name_singular }}">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancel</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@stop

@section('javascript')
    <!-- DataTables -->
    <script>
        @if (!$dataType->server_side)
            $(document).ready(function () {
                $('#dataTable').DataTable({
                "order": [] ,
                "language": {
                    "sProcessing": "处理中...",
                    "sLengthMenu": "显示 _MENU_ 项结果",
                    "sZeroRecords": "没有匹配结果",
                    "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
                    "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项",
                    "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
                    "sInfoPostFix": "",
                    "sSearch": "搜索:",
                    "sUrl": "",
                    "sEmptyTable": "表中数据为空",
                    "sLoadingRecords": "载入中...",
                    "sInfoThousands": ",",
                    "oPaginate": {
                        "sFirst": "首页",
                        "sPrevious": "上页",
                        "sNext": "下页",
                        "sLast": "末页"
                    },
                    "oAria": {
                        "sSortAscending": ": 以升序排列此列",
                        "sSortDescending": ": 以降序排列此列"
                    }

                }
            });
            });
        @endif

        $('td').on('click', '.delete', function (e) {
            var form = $('#delete_form')[0];

            form.action = parseActionUrl(form.action, $(this).data('id'));

            $('#delete_modal').modal('show');
        });

        function parseActionUrl(action, id) {
            return action.match(/\/[0-9]+$/)
                ? action.replace(/([0-9]+$)/, id)
                : action + '/' + id;
        }
    </script>
@stop
