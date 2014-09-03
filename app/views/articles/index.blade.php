@extends('layouts.master')

@section('head')
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
    {{-- HTML::style('css/jquery-ui-smoothness.css') --}}

    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    {{-- HTML::script('js/jquery-ui.js') --}}

    <script>
        (function($){

            $(document).on('ready', iniciar);

            function iniciar() {
                $('.pagination').addClass('btn-toolbar');
                $('.pagination ul').addClass('btn-group');
                $('.pagination ul li').addClass('btn btn-default');

                $('.tabs').tabs({ active: 1 });

                $('.acordion').accordion({
                    heightStyle: "content"
                });

                $('.auk-imagen').on('click', function(){
                    var url = "{{ url('articles/image') }}" + "/" + $(this).attr('id') ;
                    $('#imagenModal .modal-body').load( url );
                });

                $('#examinar1').on('click', function(){
                    $('#branchesModal .modal-body').load( "{{  url('branches/select?campo1=branch&campo2=branch_id') }}" );
                });

                $("#btnFiltrar").on('click', function(){
                    $(".acordion").toggle("slow");
                });
            }

        })(jQuery);
    </script>

@stop

@section('content')

    <h1>Informe de Artículos</h1>
    <div style="margin-bottom:1em;">
        {{ Form::open(array('url' => 'articles/search', 'method' => 'get')) }}
            <div class="input-group">
              <span class="input-group-addon">
                <input type="radio" name="filterBy" value="id" /> Por código
              </span>
              <span class="input-group-addon">
                <input type="radio" name="filterBy" value="name" checked /> Por nombre
              </span>
              <span class="input-group-addon">
                <input type="radio" name="filterBy" value="comments" /> Por adicionales
              </span>

              {{ Form::text('search', '', array('placeholder' => 'Buscar...', 'class' => 'form-control')) }}
              <span class="input-group-btn">
                <button class="btn btn-default" type="submit">Buscar</button>
              </span>
            </div><!-- /input-group -->
        {{ Form::close() }}

        @if(isset($filtro))
            <div class="alert alert-dismissable alert-info">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              {{ $filtro }}
            </div>
        @endif
    </div>

    @foreach($articles as $article)
    <div class="col-lg-6 articulo">
        <div class="panel panel-primary">
          <div class="panel-heading">
                <span class="glyphicon glyphicon-send"></span>
                {{ $article->name }}
          </div>
          <div class="panel-body">

            <div class="tabs">
              <ul>
                {{ '<li><a href="#tab1-'. $article->id .'"><span>Datos generales</span></a></li>' }}
                {{ '<li><a href="#tab2-'. $article->id .'"><span>Stock disponible</span></a></li>' }}
                {{ '<li><a href="#tab3-'. $article->id .'"><span>Stock en pendientes</span></a></li>' }}
              </ul>

              <div id="tab1-{{ $article->id }}">
                <table class="table table-bordered table-hover">
                    <tr>
                        <th>Código</th>
                        <th>Medida</th>
                        <th>Costo</th>
                        <th>Precio</th>
                        <th>IVA</th>
                    </tr>
                    <tr>
                        <td>{{ $article->id }}</td>
                        <td>{{ $article->unit }}</td>
                        <td>{{ number_format($article->cost, 2, ',', '.') }}</td>
                        <td>{{ number_format($article->price, 2, ',', '.') }}</td>
                        <td>{{ $article->iva }}%</td>
                    </tr>
                </table>

                @if(!empty($article->comments))
                    <p class="well">{{ $article->comments }}</p>
                @endif

                <a href="{{ url('articles/show-changes/'. $article->id) }}" class="link">
                    <span class="glyphicon glyphicon-road"></span>
                    Ver historial de cambios
                </a>
              </div> <!-- /#tab1 -->

              <div id="tab2-{{ $article->id }}">
                <div class="article-image">
                    @if(isset($article->image()->first()->image))
                        {{ '<img src="'. url('img/articles/'. $article->image()->first()->image) .'" class="img-rounded">' }}
                    @else
                        <!-- <img src="http://placehold.it/150x150" /> -->
                        <img src="{{ url('img/150x150.gif') }}" />
                    @endif
                </div>

                <div style="display:inline-block;">

                    <h3>COP$ {{ number_format($article->price, 2, ',', '.') }}</h3>

                    @if(Auth::check() && (Auth::user()->permitido('administrador') || Auth::user()->permitido('remisionero')))

                         {{ Form::open(array('url' => 'cart/add', 'class' => 'form-inline')) }}

                            {{ Form::text('id', $article->id, array('class' => 'hidden')) }}

                            <div class="col-xs-9 alcarrito">
                                {{ Form::input('number', 'cantidad', '1.00', array('class' => 'form-control input-sm', 'min' => '0.01', 'step' => '0.01', 'max' => '99999999999999.99', 'title' => 'Cantidad', 'required')) }}
                            </div>

                            <button type="submit" class="btn btn-success btn-sm">
                                <span class="glyphicon glyphicon-shopping-cart"></span>
                            </button>

                        {{ Form::close() }}

                    @endif

                </div>

                <div>
                    <table class="table table-stripped table-hover">
                        <tr>
                            <th>Sucursal</th>
                            <th>Stock disponible</th>
                        </tr>
                        @foreach($article->stocks as $stock)
                            <tr>
                                <td>{{ $stock->branch->name }}</td>
                                <td>{{ $article->disponible($stock->branch) .' '. $article->unit }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>

              </div> <!-- /#tab2 -->

              <div id="tab3-{{ $article->id }}">
                <div class="acordion">
                    @foreach($branches as $branch)
                        <h3>{{ $branch->name }}</h3>
                        <div>
                            <table class="table table-stripped table-hover">
                                <tr>
                                    <th>Tipo de movimiento</th>
                                    <th>Cantidad pendiente</th>
                                </tr>
                                <tr>
                                    <td>Compra</td>
                                    <td>{{ $article->inPurchases($branch) .' '. $article->unit }}</td>
                                </tr>
                                <tr>
                                    <td>Venta</td>
                                    <td>{{ $article->inSales($branch) .' '. $article->unit }}</td>
                                </tr>
                                    <td>Origen de rotación</td>
                                    <td>{{ $article->inRotationsFrom($branch) .' '. $article->unit }}</td>
                                </tr>
                                    <td>Destino de rotación</td>
                                    <td>{{ $article->inRotationsTo($branch) .' '. $article->unit }}</td>
                                </tr>
                                    <td>Daño</td>
                                    <td>{{ $article->inDamages($branch) .' '. $article->unit }}</td>
                                </tr>
                            </table>
                        </div>
                    @endforeach
                </div> <!-- /.acordion -->
              </div> <!-- /#tab3 -->

            </div> <!-- /#tabs -->

          </div> <!-- /.panel-body -->

            @if(Auth::check() && (Auth::user()->permitido('administrador') || Auth::user()->permitido('remisionero')))

              <div class="panel-footer">

                    {{ '<a href="'. url('articles/edit/'. $article->id) .'" class="btn btn-primary btn-sm">
                        <span class="glyphicon glyphicon-edit"></span>
                        Editar
                    </a>' }}

                    {{ '<a href="#imagenModal" data-toggle="modal" class="btn btn-info btn-sm auk-imagen" id="'. $article->id .'">
                        <span class="glyphicon glyphicon-picture"></span>
                        Cambiar imagen
                    </a>' }}

                    {{ '<a href="'. url('articles/excel-by-article/'. $article->id) .'" class="btn btn-success btn-sm auk-imagen" id="'. $article->id .'">
                        <span class="glyphicon glyphicon-export"></span>
                        Exportar stock
                    </a>' }}

                    @if(isset($article->image()->first()->image))
                        {{ '<a href="'. url('articles/quitar-imagen/'. $article->id) .'" class="btn btn-danger btn-sm" id="'. $article->id .'">
                            <span class="glyphicon remove"></span>
                            Quitar imagen
                        </a>' }}
                    @endif

              </div> <!-- /.panel.footer -->

            @endif

        </div> <!-- /.panel.panel-primary -->

    </div> <!-- /.col-lg-6 -->
    @endforeach

    <?php
        if(isset($input)) {
            echo $articles->appends(array_except($input, 'page'))->links();
        } else {
            echo $articles->links();
        }
    ?>

    <!-- Modal -->
      <div class="modal fade" id="imagenModal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="false">&times;</button>
              <h4 class="modal-title">Cambiar imagen</h4>
            </div>
            <div class="modal-body">
              No se ha podido cargar el formulario para cambiar la imagen.
            </div>
          </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
      </div><!-- /.modal -->

@stop