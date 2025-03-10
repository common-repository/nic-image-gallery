/*var j = jQuery.noConflict();

j(document).ready(function() {
	alert('ready');
    // bind form using ajaxForm and the form ID
    j('#post').ajaxForm( { beforeSubmit: validate } );
});

function validate() {
    var usernameValue = j('input[name=content]').fieldValue();
    // usernameValue and passwordValue are arrays but we can do simple
    // "not" tests to see if the arrays are empty
	alert(usernameValue.length);
	return false;
	
   if (!usernameValue[0]) {
        alert('Please enter a value for the Name');
        return false;
    }
}*/

jQuery(document).ready(function($){
		function reorderImages(){
			//reorder images
			$('#droppable .image-entry').each(function(i){
				//rewrite attr
				var num = i+1;
				$(this).find('.get-image').attr('data-num',num);
				$(this).find('.del-image').attr('data-num',num);
				$(this).find('div.img-preview').attr('data-num',num);
				var $input = $(this).find('input');
				$input.attr('name','image'+num).attr('id','image'+num).attr('data-num',num);
			});
		}

		if('draggable' in document.createElement('span')) {
			function handleDragStart(e) {
			  this.style.opacity = '0.4';  // this / e.target is the source node.
			}

			function handleDragOver(e) {
			  if (e.preventDefault) {
			    e.preventDefault(); // Necessary. Allows us to drop.
			  }
			  e.dataTransfer.dropEffect = 'move';  // See the section on the DataTransfer object.
			  return false;
			}

			function handleDragEnter(e) {
			  // this / e.target is the current hover target.
			  this.classList.add('over');
			}

			function handleDragLeave(e) {
				var rect = this.getBoundingClientRect();
	           // Check the mouseEvent coordinates are outside of the rectangle
	           if(e.x > rect.left + rect.width || e.x < rect.left
	           || e.y > rect.top + rect.height || e.y < rect.top) {
	               this.classList.remove('over');  // this / e.target is previous target element.
	           }
			}

			function handleDrop(e) {
			  // this / e.target is current target element.
			  if (e.stopPropagation) {
			    e.stopPropagation(); // stops the browser from redirecting.
			  }
			  // Don't do anything if dropping the same column we're dragging.
			  if (dragSrcEl != this) {
			    // Set the source column's HTML to the HTML of the column we dropped on.
			    dragSrcEl.innerHTML = this.innerHTML;
			    this.innerHTML = e.dataTransfer.getData('text/html');
			    reorderImages();

			  }
			  // See the section on the DataTransfer object.
			  return false;
			}

			function handleDragEnd(e) {
			  // this/e.target is the source node.
			  this.style.opacity = '1';
			  [].forEach.call(cols, function (col) {
			    col.classList.remove('over');
			  });
			}

			var dragSrcEl = null;

			function handleDragStart(e) {
			  // Target (this) element is the source node.
			  this.style.opacity = '0.4';
			  dragSrcEl = this;
			  e.dataTransfer.effectAllowed = 'move';
			  e.dataTransfer.setData('text/html', this.innerHTML);
			}

			var cols = document.querySelectorAll('#droppable .image-entry');
			[].forEach.call(cols, function(col) {
			  col.addEventListener('dragstart', handleDragStart, false);
			  col.addEventListener('dragenter', handleDragEnter, false);
			  col.addEventListener('dragover', handleDragOver, false);
			  col.addEventListener('dragleave', handleDragLeave, false);
			  col.addEventListener('drop', handleDrop, false);
	  		  col.addEventListener('dragend', handleDragEnd, false);
			});
		}else{
			  $( "#droppable" ).sortable({
			  	opacity: 0.4, 
			    cursor: 'move',
			    update: function(event, ui) {
			    	reorderImages()
			    }
			  });
		}
		

		
	});