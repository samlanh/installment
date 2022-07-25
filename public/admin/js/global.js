
	
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