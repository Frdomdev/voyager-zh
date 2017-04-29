<div class="container-fluid">
    <div class="alert alert-info">
        <strong>{{trans('voyager.How To Use')}}:</strong>
        <p>{{trans('voyager.You can output')}} {{ !empty($menu) ? 'this' : 'a' }} {{trans('voyager.menu anywhere on your site by calling')}} <code>Menu::display('{{ !empty($menu) ? $menu->name : 'name' }}')</code></p>
    </div>
</div>