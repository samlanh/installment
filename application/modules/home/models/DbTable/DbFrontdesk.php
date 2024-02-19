<?php

class Home_Model_DbTable_DbFrontdesk extends Zend_Db_Table_Abstract
{
    function frontdeskGetSaleinfo($data){
        $db = $this->getAdapter();
       

        $sql="SELECT 
            c.name_kh,
            (SELECT name_kh FROM ln_view WHERE type=11 AND key_code=c.sex LIMIT 1) clientGender,
            c.phone,
            DATE_FORMAT(c.dob,'%d-%m-%Y') as dob,
            (SELECT name_kh FROM ln_view WHERE type=23 AND key_code=c.client_d_type LIMIT 1) client_d_type,
            c.nation_id,
            c.client_issuedateid,
            c.nationality,
            c.street,
            c.house,
           

            (SELECT
					     `village`.`village_namekh`
					   FROM `ln_village` `village`
					   WHERE (`village`.`vill_id` = `c`.`village_id`
					                                 )
					   LIMIT 1) AS `client_village`,
				   (SELECT
				     `comm`.`commune_namekh` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`com_id`)
				   LIMIT 1) AS `client_commune`,
				  (SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`dis_id`)
				   LIMIT 1) AS `client_district`,
				  (SELECT
				     `provi`.`province_kh_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`pro_id`)
				   LIMIT 1) AS `client_province`,


            (SELECT
				     `village`.`village_namekh`
				   FROM `ln_village` `village`
				   WHERE (`village`.`vill_id` = `c`.`qvillage`)
				   LIMIT 1) AS `joint_village`,

    			  (SELECT
				     `comm`.`commune_namekh` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`dcommune`)
				   LIMIT 1) AS `join_commune`,
    			 (SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`adistrict`) LIMIT 1) AS `join_district`,
    			(SELECT
				     `provi`.`province_kh_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`cprovince`) LIMIT 1) AS `join_province`,	
    				
				 

            c.hname_kh,
            (SELECT name_kh FROM ln_view WHERE type=11 AND key_code=c.ksex LIMIT 1) joinGender,
            DATE_FORMAT(c.dob_buywith,'%d-%m-%Y') dob_buy_width,
            c.p_nationality,
            c.lphone,
            (SELECT name_kh FROM ln_view WHERE type=23 AND key_code=c.joint_doc_type LIMIT 1) AS joint_doc_type,
            c.rid_no,
            c.join_issuedateid,
            c.nationality,
            c.ghouse,
            c.dstreet,
            c.qvillage,
            c.dcommune,
            c.adistrict,
            c.cprovince,
            c.cprovince,
            c.remark,
            s.price_before,
            s.discount_amount,
            s.discount_percent,
            s.price_sold,
            (SELECT vs.totalPrincipalPaid FROM v_getsaleprincipalpaid vs WHERE  vs.saleId=s.id LIMIT 1) AS principalPaid,
            s.total_duration,
            s.interest_rate,
            s.payment_method,
            (SELECT name_kh FROM `ln_view` WHERE key_code =s.payment_id AND type = 25 limit 1) AS paymenttype,
            s.staff_id,
            (SELECT co_khname FROM ln_staff WHERE co_id=s.staff_id LIMIT 1) AS staff_name,
            s.is_cancel,
            s.buy_date,
            DATE_FORMAT(s.buy_date,'%d-%m-%Y') as buy_date,
            DATE_FORMAT(s.agreement_date,'%d-%m-%Y') as agreement_date,
            s.amount_build,
            DATE_FORMAT(s.build_start,'%d-%m-%Y') as build_start,
           
            s.is_issueplong,
            s.is_receivedplong,
            
            (SELECT rp.id FROM ln_receiveplong rp WHERE rp.sale_id = s.id  AND status=1 LIMIT 1) AS isReceivePlong,
            
            (SELECT rp.layout_type FROM ln_receiveplong rp WHERE rp.sale_id = s.id AND status=1 LIMIT 1) AS layout_type,
            (SELECT rp.create_date FROM ln_receiveplong rp WHERE rp.sale_id = s.id AND status=1 LIMIT 1) AS receivePlongDate,
           
            rh.id AS isReceiveHouse,
            rh.water_start  AS water_start,
            rh.electric_start AS electric_start,

            DATE_FORMAT(rh.issue_date,'%d-%m-%Y')  AS receive_house_date,
            rh.payment_id
            
            FROM 
            (ln_sale s
                INNER JOIN ln_client c 
                ON c.client_id=s.client_id)
                LEFT JOIN ln_issue_house rh
                ON rh.sale_id = s.id
            WHERE 1 ";
        if(!empty($data['saleId'])){
            $sql.=" AND s.id=".$data['saleId'];
        }
        if (!empty($data['propertyId'])) {
            $sql .= ' AND house_id=' . $data['propertyId'];
        }
        return $db->fetchRow($sql);
    }
    function frontDeskGetPropertyInfo($data)
    {
        $db = $this->getAdapter();
        $sql ="SELECT 
            (SELECT project_name FROM ln_project WHERE br_id=p.branch_id LIMIT 1) AS project_name,
            (SELECT pt.type_nameen FROM `ln_properties_type` pt WHERE pt.id=p.property_type LIMIT 1) AS property_type,
            hardtitle,
            land_code,
            land_address,
            street as property_street,
            price,
            land_size,
            width,
            height,
            is_lock,
            buildPercentage
        FROM ln_properties p WHERE 1 ";

        if(!empty($data['propertyId'])) {
            $sql.=" AND id=". $data['propertyId'];
        }
        return $db->fetchRow($sql);
    }
    function frontdeskResultInfo($data){
       $results = array(
            'propertyResult' => $this->frontDeskGetPropertyInfo($data),
            'saleResult' => $this->frontdeskGetSaleinfo($data),
        );
        return $results;
       
    }
    
	
}