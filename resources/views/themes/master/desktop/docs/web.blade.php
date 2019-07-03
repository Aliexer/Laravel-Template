{{--
 * @author      Archie Disono (webmonsph@gmail.com)
 * @link        https://github.com/disono/Laravel-Template
 * @license     https://github.com/disono/Laravel-Template/blob/master/LICENSE
 * @copyright   Webmons Development Studio
--}}

@extends(currentTheme() . 'layouts.master')

@section('content')
    <div class="container">
        <div class="row p-3 rounded shadow-sm bg-white">
            <div class="col">
                <div class="container">
                    @includeTheme('docs.html')
                    @includeTheme('docs.laravel')
                </div>
            </div>
        </div>
    </div>
@endsection