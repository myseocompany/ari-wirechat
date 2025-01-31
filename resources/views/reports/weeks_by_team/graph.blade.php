<!--  
	*
	*    Tabla reportes
	*
	-->

<script src="https://cdn.zingchart.com/zingchart.min.js"></script>
  <style>
    
 
    .chart--container {
      height: 400px;
      width: 100%;
      min-height: 150px;
    }
 
    .zc-ref {
      display: none;
    }
  </style>


<!-- Inicio del gráfico -->
<div id="myChart" class="chart--container">
    
  </div>
  <script>

    colors = ['rgba(35, 35, 59, 1)', 'rgba(44, 66, 104, 1)', 'rgba(0, 123, 186, 1)', 'rgba(0, 169, 226, 1)', 'rgba(124, 205, 244, 1)', 'rgba(188, 227, 250, 1)', 'rgba(155, 156, 155, 1)', 'rgba(178, 176, 176, 1)', 'rgba(197, 198, 198, 1)', 'rgba(235, 235, 235, 1)'];

 
    ZC.LICENSE = ["569d52cefae586f634c54f86dc99e6a9", "b55b025e438fa8a98e32482b5f768ff5"]; // window.onload event for Javascript to run after HTML
    // because this Javascript is injected into the document head
    window.addEventListener('load', () => {
      // Javascript code to execute after DOM content
 
      // full ZingChart schema can be found here:
      // https://www.zingchart.com/docs/api/json-configuration/
      const myConfig = {
        type: 'bar',
        title: {
          text: '',
          fontSize: 24,
          color: '#5d7d9a'
        },
        legend: {
          draggable: true,
        },

        scaleX: {
          // set scale label
          label: {
            text: 'Weeys'
          },<?php $i=0; ?>
          // convert text on scale indices
          
          labels: [@for($i=0;$i< $weeks; $i++) '{{$weeks_array[$i][0]}}' @if($i <($weeks-1)) , @endif @endfor]
          
        },
        scaleY: {
          // scale label with unicode character
          label: {
            text: 'Points'
          }
        },

        plot: {
          // animation docs here:
          // https://www.zingchart.com/docs/tutorials/design-and-styling/chart-animation/#animation__effect
          animation: {
            effect: 'ANIMATION_EXPAND_BOTTOM',
            method: 'ANIMATION_STRONG_EASE_OUT',
            sequence: 'ANIMATION_BY_NODE',
            speed: 275,
          }
        },
        
        series: [
          
          {
            // plot 1 values, linear data
            values: [@for($i=0;$i<$weeks; $i++){{$data[$i]}} @if($i<($weeks-1)) , @endif  @endfor],
            text: 'Points',
            backgroundColor: colors[{{$i}}]
          }
          
          
        ]
      };
 
      // render chart with width and height to
      // fill the parent container CSS dimensions
      zingchart.render({
        id: 'myChart',
        data: myConfig,
        height: '100%',
        width: '100%'
      });
    });
  </script>

<!--- Fin del gráfico --->
<!--Chart Placement[2]-->