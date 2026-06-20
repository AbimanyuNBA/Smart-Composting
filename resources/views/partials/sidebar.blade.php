<div class="sidebar">
    <div class="icon-box text-success" style="margin-bottom: 30px;">
        <i class="bi bi-house-door-fill"></i>
    </div>
    
    <a href="/dashboard" class="icon-box {{ request()->is('dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid-fill"></i>
    </a>
    
    <a href="/batch/create" class="icon-box {{ request()->is('batch/create') ? 'active' : '' }}">
        <i class="bi bi-plus-square"></i>
    </a>
    
    <!-- <a href="#" class="icon-box">
        <i class="bi bi-image"></i>
    </a> -->
    
    <div style="flex-grow: 1;"></div>
    
    <!-- <a href="#" class="icon-box"><i class="bi bi-gear"></i></a>
    <a href="#" class="icon-box"><i class="bi bi-person-circle"></i></a> -->
</div>