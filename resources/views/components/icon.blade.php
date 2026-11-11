@props(['name'])
<svg {{ $attributes->merge(['width' => 20, 'height' => 20, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.7', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
@switch($name)
@case('spark')<path d="M12 0.8l2.4 7.3 6.6-3.7-3.7 6.6 6 2-6.9 2 3.6 6.7-6.5-3.8L12 23l-2-6-6.7 3.7 3.7-6.6-6.2-2 7-2.1L4 3.5l6.5 3.8Z" fill="currentColor" stroke="none"/>@break
@case('bag')<path d="M5 8h14l1.5 13h-17L5 8Z"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/>@break
@case('arrow')<path d="M4 12h15m-6-6 6 6-6 6"/>@break
@case('up-right')<path d="M6 18 18 6M7 6h11v11"/>@break
@case('menu')<path d="M4 7h16M4 12h16M4 17h16"/>@break
@case('close')<path d="m6 6 12 12M18 6 6 18"/>@break
@case('box')<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm0 18v-9m-8-4.5 8 4.5 8-4.5M8 5.3l8 4.4"/>@break
@case('truck')<path d="M3 5h12v13H3zm12 5h4l3 4v4h-7"/><circle cx="7" cy="18" r="2" fill="var(--bg,#151319)"/><circle cx="18" cy="18" r="2" fill="var(--bg,#151319)"/>@break
@case('pen')<path d="m14 6 4 4M4 20l4.5-1 12-12a2.8 2.8 0 0 0-4-4l-12 12L4 20Z"/>@break
@case('check')<path d="m4.5 12 5 5 10-10"/>@break
@case('upload')<path d="M12 16V4m-5 5 5-5 5 5M4 15v5h16v-5"/>@break
@case('dashboard')<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>@break
@case('users')<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m20 0v-2a4 4 0 0 0-3-4m-3-11a4 4 0 0 1 0 8"/><circle cx="9" cy="8" r="4"/>@break
@case('cash')<rect x="2.5" y="5" width="19" height="14" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M6 12h.1m11.8 0h.1"/>@break
@case('clock')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
@endswitch
</svg>
