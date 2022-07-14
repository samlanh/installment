
	
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
	function getAllBudgetItem(urlGetBudgetItem){
		dojo.xhrPost({
			url:urlGetBudgetItem,	
			handleAs:"json",
			load: function(data) {
				ItemStore  = getDataStorefromJSON('id','name', data);		
			    dijit.byId('budgetItem').set('store',ItemStore);
			},
			error: function(err){
			}
		});
	}