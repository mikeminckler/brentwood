<div class="p-4 sticky bottom-0 bg-gray-100 shadow z-3">


    <a href="/livestreams" class="side-menu-link" >
        <div class="pr-2"><i class="fab fa-youtube"></i></div>
        <div class="">Livestreams</div>
    </a>

    <a href="/blogs" class="side-menu-link" >
        <div class="pr-2"><i class="fas fa-blog"></i></div>
        <div class="">Blogs</div>
    </a>

    <a href="/inquiries" class="side-menu-link" >
        <div class="pr-2"><i class="fas fa-question-circle"></i></div>
        <div class="">Inquiries</div>
    </a>
    
    <a href="/users" class="side-menu-link">
        <div class="pr-2"><i class="fas fa-users"></i></div>
        <div>User Management</div>
    </a>

    <a href="/permissions" class="side-menu-link">
        <div class="pr-2"><i class="fas fa-user-lock"></i></div>
        <div>Page Permissions</div>
    </a>

    <a href="/roles" class="side-menu-link">
        <div class="pr-2"><i class="fas fa-user-tag"></i></div>
        <div>Role Management</div>
    </a>
    
    @if ( auth()->user()->hasRole('admin'))
        <div class="">@{{ $store.state.wsState }}</div>
    @endif
</div>
