@if (hasPermission(getRouteName().".{$crud->route}.*", $crud->with_acl))
    <li class='sidebar-item @isActive([getRouteName().".{$crud->route}.read", getRouteName().".{$crud->route}.create", getRouteName().".{$crud->route}.update"], "selected")'>
        <a class='sidebar-link has-arrow' href="javascript:void(0)" aria-expanded="false">
            <i class="{{ $crud->icon }}"></i>
            <span class="hide-menu">{{ __(\Illuminate\Support\Str::plural(ucfirst($crud->name))) }}</span>
        </a>
        <ul aria-expanded="false" class="collapse first-level base-level-line">

            @if (hasPermission(getRouteName().".{$crud->route}.read", $crud->with_acl))
                <li class="sidebar-item @isActive(getRouteName().'.'.$crud->route.'.read')">
                    <a href="@route(getRouteName().'.'.$crud->route.'.read')" class="sidebar-link @isActive(getRouteName().'.'.$crud->route.'.read')">
                        <span class="hide-menu"> {{ __('List') }} </span>
                    </a>
                </li>
            @endif

            @if($crud->create and hasPermission(getRouteName().".{$crud->route}.create", $crud->with_acl))
                <li class="sidebar-item @isActive(getRouteName().'.'.$crud->route.'.create')">
                    <a href="@route(getRouteName().'.'.$crud->route.'.create')" class="sidebar-link @isActive(getRouteName().'.'.$crud->route.'.create')">
                        <span class="hide-menu"> {{ __('Create') }} </span>
                    </a>
                </li>
            @endif
        </ul>
    </li>
@endif
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>