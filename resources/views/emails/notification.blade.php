@extends('emails.layout')

@section('content')
    @if(isset($greeting))
        <p style="font-size: 18px; font-weight: 600; color: #2c3e50; margin-bottom: 20px;">{{ $greeting }}</p>
    @endif

    @if(isset($introLines) && is_array($introLines))
        @foreach($introLines as $line)
            <p>{!! $line !!}</p>
        @endforeach
    @endif

    @if(isset($actionText) && isset($actionUrl))
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $actionUrl }}" class="email-button">{{ $actionText }}</a>
        </div>
    @endif

    @if(isset($outroLines) && is_array($outroLines))
        @foreach($outroLines as $line)
            <p>{!! $line !!}</p>
        @endforeach
    @endif

    @if(isset($salutation))
        <p style="margin-top: 30px;">{{ $salutation }}</p>
    @endif
@endsection

