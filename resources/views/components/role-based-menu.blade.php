<div class="kt-menu flex flex-col w-full gap-1.5 px-3.5" data-kt-menu="true">
    @foreach($menuItems as $item)
        @if(isset($item['children']))
            <!-- Menu item with children -->
            <div class="kt-menu-item kt-menu-item-accordion" data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click">
                <div class="kt-menu-link gap-2.5 py-2 px-2.5 rounded-md border border-transparent">
                    <span class="kt-menu-icon items-start text-secondary-foreground text-lg kt-menu-item-here:text-foreground kt-menu-item-show:text-foreground kt-menu-link-hover:text-foreground">
                        <i class="ki-filled {{ $item['icon'] }}"></i>
                    </span>
                    <span class="kt-menu-title font-medium text-sm text-foreground kt-menu-item-here:text-mono kt-menu-item-show:text-mono kt-menu-link-hover:text-mono">
                        {{ $item['title'] }}
                    </span>
                    <span class="kt-menu-arrow text-muted-foreground kt-menu-item-here:text-foreground kt-menu-item-show:text-foreground kt-menu-link-hover:text-foreground">
                        <span class="inline-flex kt-menu-item-show:hidden">
                            <i class="ki-filled ki-down text-xs"></i>
                        </span>
                        <span class="hidden kt-menu-item-show:inline-flex">
                            <i class="ki-filled ki-up text-xs"></i>
                        </span>
                    </span>
                </div>
                <div class="kt-menu-accordion gap-px ps-7">
                    @foreach($item['children'] as $child)
                        <div class="kt-menu-item">
                            <a class="kt-menu-link py-2 px-2.5 rounded-md border border-transparent kt-menu-item-active:border-border kt-menu-item-active:bg-background kt-menu-link-hover:bg-background kt-menu-link-hover:border-border" 
                               href="{{ route($child['route']) }}">
                                <span class="kt-menu-title text-sm text-foreground kt-menu-item-active:text-mono kt-menu-link-hover:text-mono">
                                    {{ $child['title'] }}
                                </span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <!-- Single menu item -->
            <div class="kt-menu-item">
                <a class="kt-menu-link gap-2.5 py-2 px-2.5 rounded-md border border-transparent kt-menu-item-active:border-border kt-menu-item-active:bg-background kt-menu-link-hover:bg-background kt-menu-link-hover:border-border" 
                   href="{{ route($item['route']) }}">
                    <span class="kt-menu-icon items-start text-lg text-secondary-foreground kt-menu-item-active:text-foreground kt-menu-item-here:text-foreground kt-menu-item-show:text-foreground kt-menu-link-hover:text-foreground">
                        <i class="ki-filled {{ $item['icon'] }}"></i>
                    </span>
                    <span class="kt-menu-title text-sm text-foreground font-medium kt-menu-item-here:text-mono kt-menu-item-show:text-mono kt-menu-link-hover:text-mono">
                        {{ $item['title'] }}
                    </span>
                </a>
            </div>
        @endif
    @endforeach
</div>
