
	
function getAllCategory(urlGetCategory){
		dojo.xhrPost({
			url:urlGetCategory,	
			handleAs:"json",
			load: function(data) {
				categoryStore  = getDataStorefromJSON('id','name', data);		
			    dijit.byId('categoryId').set('store',categoryStore);
			},
			error: function(err){
			}
		});
	}
	function getAllMeasure(urlGetMeasure){
		dojo.xhrPost({
			url:urlGetMeasure,	
			handleAs:"json",
			load: function(data) {
				measureStore  = getDataStorefromJSON('id','name', data);		
			    dijit.byId('measureId').set('store',measureStore);
			},
			error: function(err){
			}
		});
	}
	function getAllBudgetItem(urlGetBudgetItem,contentData){
		dojo.xhrPost({
			url:urlGetBudgetItem,	
			handleAs:"json",
			content:contentData,
			load: function(data) {
				ItemStore  = getDataStorefromJSON('id','name', data);		
			    dijit.byId('budgetItem').set('store',ItemStore);
			},
			error: function(err){
			}
		});
	}
	function getAllBudgetType(urlGetBudgetType,contentData){
		dojo.xhrPost({
			url:urlGetBudgetType,	
			handleAs:"json",
			content:contentData,
			load: function(data) {
				budgetTypeStore  = getDataStorefromJSON('id','name', data);		
			    dijit.byId('budgetType').set('store',budgetTypeStore);
			},
			error: function(err){
			}
		});
	}
	function getAllProductStoreFunction(urlGetAllProduct,objectContentFilter){
		dijit.byId('productId').reset();
		productStore  = getDataStorefromJSON('id','name', [] );
		dijit.byId('productId').set('store', productStore);
		dojo.xhrPost({
			url:urlGetAllProduct,	
			content:objectContentFilter,		    
			handleAs:"json",
			load: function(data) {
				productStore  = getDataStorefromJSON('id','name', data);		
				dijit.byId('productId').set('store', productStore);
			},
			error: function(err) {
			}
		});
	}
	function getAllPObyBranch(urlGetAllPO,objectContentFilter){
		dojo.xhrPost({
			url:urlGetAllPO,	
			content:objectContentFilter,		    
			handleAs:"json",
			load: function(data) {
				purchaseStore  = getDataStorefromJSON('id','name', data);		
				dijit.byId('purId').set('store', purchaseStore);

			},
			error: function(err) {
			}
		});
	}
	function getAllStaffbyBranch(urlGetAllStaff,objectContentFilter){
		dojo.xhrPost({
			url:urlGetAllStaff,	
			content:objectContentFilter,		    
			handleAs:"json",
			load: function(data) {
				staffStore  = getDataStorefromJSON('id','name', data);		
				dijit.byId('staffWithdraw').set('store', staffStore);

			},
			error: function(err) {
			}
		});
	}
	function getAllContractorbyBranch(urlGetAllContractor,objectContentFilter){
		dojo.xhrPost({
			url:urlGetAllContractor,	
			content:objectContentFilter,		    
			handleAs:"json",
			load: function(data) {
				staffStore  = getDataStorefromJSON('id','name', data);		
				dijit.byId('contractor').set('store', staffStore);

			},
			error: function(err) {
			}
		});
	}
	
	function getAllWorkType(urlGetAllContractor,objectContentFilter,selected=null){
		dojo.xhrPost({
			url:urlGetAllContractor,	
			content:objectContentFilter,		    
			handleAs:"json",
			load: function(data) {
				workTypeStore  = getDataStorefromJSON('id','name', data);		
				dijit.byId('workType').set('store', workTypeStore);
				if(selected!=null){
					dijit.byId('workType').attr('value',selected)
				}

			},
			error: function(err) {
			}
		});
	}
	function addConditionOption(urlAddCondition,objectContentFilter){
		dojo.xhrPost({
			url:urlAddCondition,	
			content:objectContentFilter,
			handleAs:"json",
			load: function(data) {	
				return data;
			},
			error: function(err){
			}
		})
		
	}
	function getAllTransferinFunction(urlGetAllItems,objectContentFilter){
		dijit.byId('transferId').reset();
		itemsStore  = getDataStorefromJSON('id','name', [] );
		dijit.byId('transferId').set('store', itemsStore);
		dojo.xhrPost({
			url:urlGetAllItems,	
			content:objectContentFilter,		    
			handleAs:"json",
			load: function(data) {
				itemsStore  = getDataStorefromJSON('id','name', data);		
				dijit.byId('transferId').set('store', itemsStore);
			},
			error: function(err) {
			}
		});
	}
	function getAllDNList(urlGet,objectContentFilter,selected=null){
		dojo.xhrPost({
			url:urlGet,	
			content:objectContentFilter,		    
			handleAs:"json",
			load: function(data) {
				dnStore  = getDataStorefromJSON('id','name', data);		
				dijit.byId('dnList').set('store', dnStore);
				if(selected!=null){
					dijit.byId('dnList').attr('value',selected)
				}
			},
			error: function(err) {
			}
		});
	}

	function getAllInvoiceList(urlGet,objectContentFilter,selected=null){
		dojo.xhrPost({
			url:urlGet,	
			content:objectContentFilter,		    
			handleAs:"json",
			load: function(data) {
				alert(data);
				invoiceStore  = getDataStorefromJSON('id','name', data);		
				dijit.byId('invoiceList').set('store', invoiceStore);
				if(selected!=null){
					dijit.byId('invoiceList').attr('value',selected)
				}
			},
			error: function(err) {
			}
		});
	}
