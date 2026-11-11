@if(session('success'))
    <div class="notice success" dir="rtl" role="status"><x-icon name="check" /> {{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="notice error" dir="rtl" role="alert">
        <strong>راجع البيانات وحاول تاني.</strong>
        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
