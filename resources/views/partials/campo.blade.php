{{-- Campo de formulario: name, label, [type, value, col, req, attrs, placeholder] --}}
<div class="{{ $col ?? 'col-md-6' }}">
    <label class="form-label {{ ! empty($req) ? 'req' : '' }}" for="f_{{ $name }}">{{ $label }}</label>
    @if (($type ?? 'text') === 'textarea')
        <textarea class="form-control @error($name) is-invalid @enderror" name="{{ $name }}" id="f_{{ $name }}" rows="{{ $rows ?? 2 }}" placeholder="{{ $placeholder ?? '' }}">{{ old($name, $value ?? '') }}</textarea>
    @elseif (($type ?? 'text') === 'select')
        <select class="form-select @error($name) is-invalid @enderror" name="{{ $name }}" id="f_{{ $name }}" @if (! empty($req)) required @endif>
            @isset($vacio)<option value="">{{ $vacio }}</option>@endisset
            @foreach ($opciones as $valor => $texto)<option value="{{ $valor }}" @selected((string) old($name, $value ?? '') === (string) $valor)>{{ $texto }}</option>@endforeach
        </select>
    @else
        <input type="{{ $type ?? 'text' }}" class="form-control @error($name) is-invalid @enderror" name="{{ $name }}" id="f_{{ $name }}" value="{{ old($name, $value ?? '') }}" placeholder="{{ $placeholder ?? '' }}" @if (! empty($req)) required @endif {!! $attrs ?? '' !!}>
    @endif
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
