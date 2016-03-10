@extends($master)

@section('head')
    
@stop

@section('page')
    {{ trans('ticketit::admin.agent-index-title') }}
@stop

@section('content')
    @include('ticketit::shared.header')
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2>{{ trans('ticketit::admin.agent-index-title') }}
                {!! link_to_route(
                                    $setting->grab('admin_route').'.agent.create',
                                    trans('ticketit::admin.btn-create-new-agent'), null,
                                    ['class' => 'btn btn-primary pull-right'])
                !!}
            </h2>
        </div>

        @if ($agents->isEmpty())
            <h3 class="text-center">{{ trans('ticketit::admin.agent-index-no-agents') }}
                {!! link_to_route($setting->grab('admin_route').'.agent.create', trans('ticketit::admin.agent-index-create-new')) !!}
            </h3>
        @else
            <div id="message"></div>
            <table class="table marginize">
                <thead>
                    <tr>
                        <th>{{ trans('ticketit::admin.table-hash') }}</th>
                        <th>{{ trans('ticketit::admin.table-name') }}</th>
                        <th>{{ trans('ticketit::admin.table-categories') }}</th>
                        <th>{{ trans('ticketit::admin.table-remove-agent') }}</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($agents as $agent)
                    <tr>
                        <th scope="row">{{ $agent->id }}</th>
                        <td>{{ $agent->name }}</td>
                        <td>
                            <div class="tags-block"  id="cat-block"> 
                            @foreach($agent->categories as $category)
                                <span style="border-left: 5px solid {{ $category->color }};" class="tag">
                                    <span class="tag-name">{{ $category->name }}</span>
                                    <a name="delete" id="{{ $category->id }}" class="delete"><span title="Delete" class="glyphicon glyphicon-remove" aria-hidden="true"></span></a>
                                </span>
                            @endforeach
                            </div>

                            {!! CollectiveForm::open([
                                            'method' => 'PATCH',
                                            'route' => [
                                                        $setting->grab('admin_route').'.agent.update',
                                                        $agent->id
                                                        ],
                                            ]) !!}
                            <div class="tags-block">
                                <input type="text" list="categories" class="suggest-holder" placeholder="Add a category..." id="category">
                                <a class="save gi-8s"><span title="Save" class="glyphicon glyphicon-floppy-save" aria-hidden="true"></span></a>
                                <datalist id="categories">
                                    @foreach(\Kordy\Ticketit\Models\Category::all() as $agent_cat)
                                    <option data-value="{{ $agent_cat->id }}" data-color="{{ $agent_cat->color }}">{{ $agent_cat->name }}</option>
                                    @endforeach
                                </datalist>
                                <input type="hidden" name="category" id="category-hidden">
                                <input type="hidden" name="category-id" id="category-id-hidden">
                                <input type="hidden" name="category-color" id="category-color-hidden">
                            </div>
                            {!! CollectiveForm::close() !!}
                        </td>
                        <td>
                            {!! CollectiveForm::open([
                            'method' => 'DELETE',
                            'route' => [
                                        $setting->grab('admin_route').'.agent.destroy',
                                        $agent->id
                                        ],
                            'id' => "delete-$agent->id"
                            ]) !!}
                            {!! CollectiveForm::submit(trans('ticketit::admin.btn-remove'), ['class' => 'btn btn-danger btn-sm']) !!}
                            {!! CollectiveForm::close() !!}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        @endif
    </div>
@stop
