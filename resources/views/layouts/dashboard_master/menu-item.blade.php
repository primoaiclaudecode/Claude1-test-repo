@if(empty($item->gate) || Gate::allows($item->gate))
   @php
      $class = (in_array(Helpers::getActionName(),explode(',',$item->action))
            && (!isset($purchType) || ($purchType === 'cash' && $item->link === '/sheets/purchases/cash')
            || ($purchType === 'credit' && $item->link === '/sheets/purchases/credit'))) ? 'active' : '';
   @endphp
   
   <li class="{{ $class }}">
      <a href="{{ url($item->link) }}">{{ $item->name }}</a>
   </li>
@endif