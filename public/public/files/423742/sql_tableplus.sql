select i.propietario, count(*) from 
import_clientify_2024_02_05 i

group by i.propietario;


select user_id, count(*)
from customers c
where c.created_at IN ('2025-02-06 11:57:36', '2025-02-06 12:01:09')
GROUP by user_id
HAVING count(*)>1
order by user_id DESC;

select * 
from customers c
where c.created_at IN ('2025-02-06 11:57:36', '2025-02-06 12:01:09')
AND user_id = 113

order by user_id DESC
limit 25;

UPDATE customers 
SET user_id = 88
WHERE id IN (
    SELECT id FROM (
        SELECT id 
        FROM customers 
        WHERE created_at IN ('2025-02-06 11:57:36', '2025-02-06 12:01:09') 
        AND user_id = 11
        ORDER BY user_id DESC
        LIMIT 46
    ) AS subquery
);


select 750-704;
704;


SELECT 
    c.name, 
    c.user_id
    , 
    c.phone_wp,
    i.nombre, 
    i.propietario 
FROM customers c
LEFT JOIN import_clientify_2024_02_05 i
   ON (c.phone_wp = i.phone_wp and i.phone_wp is not NULL)
   
   # OR (c.email = i.`email 1`and i.phone_wp is not NULL ) 
  #  AND (i.phone_wp IS NOT NULL OR i.`email 1` IS NOT NULL)
WHERE 
    c.created_at IN ('2025-02-06 11:57:36', '2025-02-06 12:01:09')
    #and i.propietario not in ("Monica Román", "Nicolás nicolas@myseocompany.co", "Manuela Gonzalez Correa" )
    #and i.propietario != ""
    and user_id is null
   # limit 688
   
   ;

select 672-704;

UPDATE customers c
JOIN import_clientify_2024_02_05 i
    ON c.phone_wp = i.phone_wp 
    AND i.phone_wp IS NOT NULL
SET c.user_id = 11
WHERE c.created_at IN ('2025-02-06 11:57:36', '2025-02-06 12:01:09')
#AND i.propietario NOT IN ('Monica Román', 'Nicolás nicolas@myseocompany.co', 'Manuela Gonzalez Correa')
AND c.user_id IS NULL;


UPDATE customers c
SET c.user_id = 11
WHERE c.created_at IN ('2025-02-06 11:57:36', '2025-02-06 12:01:09')
#AND i.propietario NOT IN ('Monica Román', 'Nicolás nicolas@myseocompany.co', 'Manuela Gonzalez Correa')
AND c.user_id IS NULL;



    and (c.phone_wp IN (select distinct phone_wp from import_clientify_2024_02_05 i WHERE
    	i.phone_wp IS NOT NULL )
    	OR
    	(c.email IN (select distinct ii.`email 1` from import_clientify_2024_02_05 ii WHERE
    	ii.`email 1` IS NOT NULL ))
    );



SELECT 
    c.name, 
    cleanPhone(c.phone_wp)
FROM customers c

WHERE 
    c.created_at IN ('2025-02-06 11:57:36', '2025-02-06 12:01:09');


WITH ranked_customers AS (
    SELECT 
         
        cleanPhone(c.phone_wp) AS phone, 
        c.name AS full_name,
        c.user_id,
        ROW_NUMBER() OVER (PARTITION BY c.user_id ORDER BY c.created_at DESC) AS row_num
    FROM customers c
    WHERE c.created_at IN ('2025-02-06 11:57:36', '2025-02-06 12:01:09') and c.phone_wp is not null
    AND c.user_id IN (113, 109, 88, 11)
)
SELECT full_name, phone, user_id
FROM ranked_customers
WHERE row_num <= 13;



select cs.name, count(*) from
customer_statuses cs
left join customers c
on c.status_id = cs.id
group by cs.name
;


select * from users
where status_id=1;