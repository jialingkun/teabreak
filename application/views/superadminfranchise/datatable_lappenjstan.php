<script type="text/javascript">
	var howmuch = 0;
	var tabeldata;
	var tabeldetail;
	
	$.ajax({
          type:"post",
          url: "<?php echo base_url('superadminfranchise/get_list_stan')?>/",
          data:{},
          dataType:"json",
          success:function(response)
          {
          	var htmlinsideselect = '';
          	for (var j = response.length - 1; j >= 0; j--) {
          		if (j==response.length-1) {
          			htmlinsideselect = htmlinsideselect + '<option selected="selected" value="'+response[j].id_stan+'">'+response[j].nama_stan +' ( '+response[j].alamat+' )' +'</option>';
          		}else{
          			htmlinsideselect = htmlinsideselect + '<option value="'+response[j].id_stan+'">'+response[j].nama_stan +' ( '+response[j].alamat+' )' +'</option>';
          		}
          		
	        }
	        $("#select_stan").html(htmlinsideselect);
	        
	        tabeldata = $("#mytable").DataTable({
		      initComplete: function() {
		        var api = this.api();
		        $('#mytable_filter input')
		        .on('.DT')
		        .on('keyup.DT', function(e) {
		          if (e.keyCode == 13) {
		            api.search(this.value).draw();
		          }
		        });
		      },
		      oLanguage: {
		        sProcessing: "loading..."
		      },
		      responsive: true,
		      ajax: {
			    "type"   : "POST",
			    "data": function(data) {
				  data.tanggal_awal = $('#tanggal_awal').val();
				  data.tanggal_akhir = $('#tanggal_akhir').val();
				  data.id_stan = $('#select_stan').val();
				  data.shift = $('#shift').val();
				},
			    "url"    : "<?php echo base_url('superadminfranchise/notaData');?>",
			    "dataSrc": function (json) {
			      var return_data = new Array();
			      var total_harga_akhir = 0;
			      var no = 1;
			      howmuch = json.length;

			      for(var i=0;i< json.length; i++){
			      	var nama = json[i].nama_diskon;
			      	var kett = json[i].keterangan;
			      	nama = nama.split(' ').join('+');
			      	kett = kett.split(' ').join('+');
			        return_data.push({
			        	'no': no,
			          'id_nota': json[i].id_nota,
			          'tanggal_nota'  : {
                            "display" : uidate(json[i].tanggal_nota),
                            "real" : json[i].tanggal_nota
                          },
			          'waktu_nota' : json[i].waktu_nota,
			          'shift' : json[i].shift.charAt(0).toUpperCase() + json[i].shift.slice(1),
			          'total_harga_jual' : "Rp "+currency(json[i].total_harga),
			          'detail' : '<button onclick=detail_nota("'+json[i].id_nota+'","'+json[i].total_harga+'","'+nama+'","'+json[i].jenis_diskon+'","'+json[i].status+'","'+json[i].pembayaran+'","'+kett+'") class="btn btn-warning" style="color:white;">Detail</button> '
			        });
		    		total_harga_akhir = total_harga_akhir + parseInt(json[i].total_harga);
		    		no++;
			      }
			      $("#total_harga_akhir").html('Total Penjualan Rp '+currency(parseInt(total_harga_akhir))+',-');

			     //  var eachharga = tabeldata.columns( 2 ).data().eq( 0 );
			    	
			    	// for (var i = eachharga.length - 1; i >= 0; i--) {
			    	// 	alert(eachharga[i]);
			    	// 	var nominal = eachharga[i].replace('Rp ','');
			    	// 	nominal = nominal.replace('.','');
			    	// 	total_harga_akhir = total_harga_akhir + nominal;
			    	// }
			    	
			      return return_data;
			    }
			  },
            dom: 'Bfrtlip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        title:function(argument) {
                            return 'Data Laporan Penjualan Stan ';
                        } ,
                        messageTop: function (argument) {
                            return 'Stan : '+$("#select_stan option:selected").text()+', Tanggal : '+$("#tanggal_awal").val()+' - '+$("#tanggal_akhir").val()+", Shift : "+$("#shift option:selected").val();
                        },
                        customize: function ( xlsx ){
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];

                            // jQuery selector to add a border
                            $('row c[r*="3"]', sheet).attr( 's', '27' );

                            for (var i = 0; i < howmuch; i++) {
                              var row = i + 4;
                              $('row c[r*="'+row+'"]', sheet).attr( 's', '25' );
                            }

                        },
                        text: '<i class="fa fa-download"></i> Download Excel',
                        className: 'btn btn-success',
                        init: function(api, node, config) {
                           $(node).removeClass('dt-button');
                           $(node).removeClass('buttons-excel');
                           $(node).removeClass('buttons-html5');
                        },
                        filename: function (argument) {
                              // var standdd = $("#select_stan option:selected").text();
                              // var tgl = $("#tanggal_awal").val();

                              return 'Laporan Penjualan Stan '+$("#select_stan option:selected").text()+', Tanggal : '+$("#tanggal_awal").val()+' - '+$("#tanggal_akhir").val()+", Shift : "+$("#shift option:selected").val();
                        } ,

                        exportOptions: {
                          columns:[0,1,2,3,4,5]
                        }
                    }
                ],
                "lengthChange": true,
				  columns: [
				  {'data' : 'no'},
				    {'data': 'id_nota'},
				    {'data': 'tanggal_nota',render: {_: 'display',sort: 'real'}},
				    {'data' : 'waktu_nota'},
				    {'data': 'shift'},
				    {'data': 'total_harga_jual'},
				    {'data': 'detail','orderable':false,'searchable':false}
				  ],
	    	});
          },
          error: function (jqXHR, textStatus, errorThrown)
          {
            alert(errorThrown);
          }
      }
    );

    function refreshTable() {
    	reload_table();
    }

    function reload_table(){
	  tabeldata.ajax.reload();
	}

	function currency(x) {
	    var retVal=x.toString().replace(/[^\d]/g,'');
	    while(/(\d+)(\d{3})/.test(retVal)) {
	      retVal=retVal.replace(/(\d+)(\d{3})/,'$1'+'.'+'$2');
	    }
	    return retVal;
	  }

	 function detail_nota(id,harga_total_akhir,nama_diskon,jenis_diskon,status,pembayaran,keterangan) {
	 	// console.log(harga_total_akhir);
	 	$("#jenis_pembayaran").removeClass('badge-primary');
	 	$("#jenis_pembayaran").removeClass('badge-success');
	 	$("#jenis_pembayaran").removeClass('badge-warning');

	 	$("#status").removeClass('badge-success');
	 	$("#status").removeClass('badge-danger');

	 	if (pembayaran == 'cash') {
	 		$("#jenis_pembayaran").addClass('badge-success');
	 	}else if (pembayaran == 'debit') {
	 		$("#jenis_pembayaran").addClass('badge-warning');
	 	}else{
	 		$("#jenis_pembayaran").addClass('badge-primary');
	 	}

	 	$("#modalDetail").modal('toggle');
	 	$("#jenis_pembayaran").html(pembayaran.toUpperCase());

	 	if (status == 'void') {
	 		$("#status").addClass('badge-danger');
	 		var stat = 'VOID';
	 	}else{
	 		$("#status").addClass('badge-success');
	 		var stat = 'TIDAK VOID';
	 	}
	 	$("#status").html(stat);

	 	if (nama_diskon == 'none') {
	 		var disc = 'tidak ada diskon'; 
	 	}else{
	 		var disc = '';
	 		var jenishelp = '';
	 		var nama = nama_diskon.split(",");
	 		var jenis = jenis_diskon.split(",");

	 		for (var i = nama.length - 1; i >= 0; i--) {
	 			if (jenis[i].includes('nominal')) {
	 				jenishelp = 'potongan Rp.'+currency(jenis[i]);
	 			}else if (jenis[i].includes('persen')) {
	 				jenishelp = 'potongan '+ jenis[i].replace("persen", "")+'%';
	 			}else if (jenis[i].includes('buy1')) {
	 				jenishelp = 'promo beli 1 gratis 1';
	 			}else if (jenis[i].includes('buy2')) {
	 				jenishelp = 'promo beli 2 gratis 1';
	 			}

	 			disc = disc+'<h6>- '+nama[i].split('+').join(' ')+' ( '+jenishelp+' )</h6>';
	 			
	 		}

	 		
	 	}
	 	
	 	$("#listdiskon").html(disc);

	 	if (keterangan == 'none') {
	 		var ket = 'tidak ada keterangan';
	 	}else{
	 		var ket = keterangan.split('+').join(' ');
	 	}

	 	$("#keterangan").html(ket);
	 	$("#totalhargapernota").text("Total Harga Nota : Rp. "+currency(harga_total_akhir)+",-");
	 	if ( $.fn.DataTable.isDataTable( '#detailnota' ) ) {
	        $('#detailnota').DataTable().destroy();
	    }

	 	tabeldetail = $("#detailnota").DataTable({
	      initComplete: function() {
	        var api = this.api();
	        $('#mytable_filter input')
	        .on('.DT')
	        .on('keyup.DT', function(e) {
	          if (e.keyCode == 13) {
	            api.search(this.value).draw();
	          }
	        });
	      },
	      oLanguage: {
	        sProcessing: "loading..."
	      },
	      responsive: true,
	      ajax: {
		    "type"   : "POST",
		    "data": function(data) {
			  data.id_nota = id;
			},
		    "url"    : "<?php echo base_url('superadminfranchise/detailNotaData');?>",
		    "dataSrc": function (json) {
		      var return_data = new Array();

		      for(var i=0;i< json.length; i++){

		        return_data.push({
		          'nama_produk': json[i].nama_produk,
		          'jumlah_produk'  : json[i].jumlah_produk,
		          'kategori_produk' : json[i].kategori_produk,
		          'harga_produk' : "Rp "+currency(json[i].harga_produk),
		          'total_harga_produk' : "Rp "+currency(json[i].total_harga_produk)
		        });
		      }
		      return return_data;
		    }
		  },
	   		dom: 'Bfrtlip',
	        buttons: [
	            {
	                extend: 'copyHtml5',
	                text: 'Copy',
	                filename: 'Produk Data',
	                exportOptions: {
	                  columns:[0,1,2]
	                }
	            },{
	                extend: 'excelHtml5',
	                text: 'Excel',
	                className: 'exportExcel',
	                filename: 'Produk Data',
	                exportOptions: {
	                  columns:[0,1,2]
	                }
	            },{
	                extend: 'csvHtml5',
	                filename: 'Produk Data',
	                exportOptions: {
	                  columns:[0,1,2]
	                }
	            },{
	                extend: 'pdfHtml5',
	                filename: 'Produk Data',
	                exportOptions: {
	                  columns:[0,1,2]
	                }
	            },{
	                extend: 'print',
	                filename: 'Produk Data',
	                exportOptions: {
	                  columns:[0,1,2]
	                }
	            }
	        ],
	        "lengthChange": true,
			  columns: [
			    {'data': 'nama_produk'},
			    {'data': 'jumlah_produk'},
			    {'data': 'kategori_produk'},
			    {'data': 'harga_produk'},
			    {'data': 'total_harga_produk'}
			  ],
    	});
	 }
</script>