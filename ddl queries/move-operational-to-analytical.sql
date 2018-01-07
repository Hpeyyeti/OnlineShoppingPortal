# delete from cmpe226a6.PRODUCT_POPULARITY; ALTER TABLE cmpe226a6.PRODUCT_POPULARITY AUTO_INCREMENT = 1;
# delete from cmpe226a6.SALES; ALTER TABLE cmpe226a6.SALES AUTO_INCREMENT = 1;
# delete from cmpe226a6.feedback; ALTER TABLE cmpe226a6.feedback AUTO_INCREMENT = 1;
# delete from cmpe226a6.order_history; ALTER TABLE cmpe226a6.order_history AUTO_INCREMENT = 1;
# delete from cmpe226a6.avgrating; ALTER TABLE cmpe226a6.avgrating AUTO_INCREMENT = 1;
#
# delete from cmpe226a6.calendar; ALTER TABLE cmpe226a6.calendar AUTO_INCREMENT = 1;
# delete from cmpe226a6.customer; ALTER TABLE cmpe226a6.customer AUTO_INCREMENT = 1;
# delete from cmpe226a6.customer_location; ALTER TABLE cmpe226a6.customer_location AUTO_INCREMENT = 1;
# delete from cmpe226a6.location; ALTER TABLE cmpe226a6.location AUTO_INCREMENT = 1;
# delete from cmpe226a6.`order`; ALTER TABLE cmpe226a6.`order` AUTO_INCREMENT = 1;
# delete from cmpe226a6.product; ALTER TABLE cmpe226a6.product AUTO_INCREMENT = 1;
# delete from cmpe226a6.review; ALTER TABLE cmpe226a6.review AUTO_INCREMENT = 1;


drop procedure if exists cmpe226a6.fillDates;
delimiter |
create procedure cmpe226a6.fillDates(dateStart DATE, dateEnd DATE)
  begin
    while dateStart <= dateEnd do
      insert ignore into cmpe226a6.CALENDAR (CalendarKey, Date, Month, Quarter, DayOfWeek, Year)
      values (null, dateStart, month(dateStart), quarter(dateStart), dayofweek(dateStart), year(dateStart));
      set dateStart = date_add(dateStart, interval 1 day);
    end while;
  end;
|
delimiter ;
call cmpe226a6.fillDates('2010-01-01', '2016-12-31');

-- Table Sync Statements

insert
into cmpe226a6.PRODUCT
(ProductKey, ProductId, ProductName, Description, ProductBrandName, ProductPrice)
  select null, p.ProductId, p.ProductName, p.Description, b.BrandName, p.Price
  from cmpe226operational.PRODUCT p
    join cmpe226operational.BRAND b
      on b.BrandId = p.BrandId

on duplicate key update
  ProductName = p.ProductName
  , Description = p.Description
  , ProductBrandName = b.BrandName
  , ProductPrice = p.Price
;


insert
into cmpe226a6.CUSTOMER
(CustomerKey, CustomerId, FirstName, LastName, ContactUsername)
  select null, c.CustomerId, a.FirstName, a.LastName, c.UserName
  from cmpe226operational.CUSTOMER c
    join cmpe226operational.ADDRESSABLECONTACT a
      on c.CustomerId = a.CustomerId
    join cmpe226operational.ZIPCODE z
      on a.ZipcodeId = z.ZipcodeId

on duplicate key update
  FirstName =  a.FirstName
  , LastName = a.LastName
  , ContactUsername = c.UserName

;

insert
into cmpe226a6.CUSTOMER_LOCATION
(CustomerLocationKey, CustomerId, FirstName, LastName, ContactUsername, Email, StreetAddress, ZipCode)
  select null, c.CustomerId, a.FirstName, a.LastName, c.UserName, a.Email, a.StreetAddress, z.Zipcode
  from cmpe226operational.CUSTOMER c
    join cmpe226operational.ADDRESSABLECONTACT a
      on c.CustomerId = a.CustomerId
    join cmpe226operational.ZIPCODE z
      on a.ZipcodeId = z.ZipcodeId

on duplicate key update
  FirstName =  a.FirstName
  , LastName = a.LastName
  , ContactUsername = c.UserName
  , Email = a.Email
  , StreetAddress = a.StreetAddress
  , ZipCode = z.Zipcode

;

insert
into cmpe226a6.`ORDER`
(OrderKey, OrderId, OrderStatus, OrderAmount)
  select null, o.OrderId, o.OrderStatus, p.PaymentAmount
  from cmpe226operational.`ORDER` o
    join cmpe226operational.PAYMENT p
      on o.PaymentId = p.PaymentId

on duplicate key update
  OrderStatus =  o.OrderStatus
;

insert
into cmpe226a6.LOCATION
(LocationKey, ZipCode, City, State)
  select null, z.Zipcode, c.CityName, s.StateName
  from cmpe226operational.ZIPCODE z
    join cmpe226operational.CITY c
      on z.CityId = c.CityId
    join cmpe226operational.STATE s
      on c.StateId = s.StateId

on duplicate key update
  ZipCode =  z.Zipcode
  , City = c.CityName
  , State = s.StateName
;


insert
into cmpe226a6.SALES
(OrderKey, ProductKey, CalendarKey, CustomerLocationKey, LocationKey, UnitsSold, TotalSales)
  select oo.OrderKey, pp.ProductKey, cal.CalendarKey, cl.CustomerLocationKey, ll.LocationKey, ol.Quantity UnitsSold, ol.Quantity * ol.PriceWhenPurchased TotalSales
  from cmpe226operational.`ORDER` o
    join cmpe226operational.ORDERLINE ol
      on o.OrderId = ol.OrderId
    join cmpe226a6.PRODUCT p
      on ol.ProductId = p.ProductId
    join cmpe226operational.ADDRESSABLECONTACT ac
      on o.AddressableContactId = ac.AddressableContactId
    join cmpe226operational.ZIPCODE z
      on ac.ZipcodeId = z.ZipcodeId
    join cmpe226operational.CITY c
      on z.CityId = c.CityId
    join cmpe226operational.STATE s
      on c.StateId = s.StateId
    join cmpe226a6.customer_location cl
      on o.CustomerId = cl.CustomerId
         and ac.Email = cl.Email
         and ac.StreetAddress = cl.StreetAddress
    join cmpe226a6.ORDER oo
      on o.OrderId = oo.OrderId
    join cmpe226a6.LOCATION ll
      on z.Zipcode = ll.Zipcode
         and c.CityName = ll.City
         and s.StateName = ll.State
    join cmpe226a6.PRODUCT pp
      on ol.ProductId = pp.ProductId
    join cmpe226a6.CALENDAR cal
      on Date(o.OrderDate) = cal.Date

on duplicate key update
  CustomerLocationKey = cl.CustomerLocationKey
  , LocationKey = ll.LocationKey
  , UnitsSold = ol.Quantity
  , TotalSales = ol.Quantity * ol.PriceWhenPurchased
;

insert
into cmpe226a6.PRODUCT_POPULARITY
(ProductKey, CalendarKey, QuantitySold)
  select t.ProductKey, t.CalendarKey, t.Quantity
  from (
         select pp.ProductKey, cal.CalendarKey, sum(ol.Quantity) Quantity
         from cmpe226operational.`ORDER` o
           join cmpe226operational.ORDERLINE ol
             on o.OrderId = ol.OrderId
           join cmpe226a6.CALENDAR cal
             on Date(o.OrderDate) = cal.date
           join cmpe226a6.PRODUCT pp
             on ol.ProductId = pp.ProductId
         group
         by Date(o.OrderDate), ol.ProductId
       ) t

on duplicate key update
  QuantitySold =  t.Quantity
;


insert
ignore
into cmpe226a6.ORDER_HISTORY
(OrderKey, ProductKey, CustomerKey, CalendarKey)
  select oo.OrderKey, pp.ProductKey, cc.CustomerKey, cal.CalendarKey
  from cmpe226operational.`ORDER` o
    join cmpe226a6.`ORDER` oo
      on o.OrderId = oo.OrderId
    join cmpe226a6.customer cc
      on o.CustomerId = cc.CustomerId
    join cmpe226operational.orderline ol
      on o.OrderId = ol.OrderId
    join cmpe226a6.product pp
      on ol.ProductId = pp.ProductId
    join cmpe226a6.CALENDAR cal
      on Date(o.OrderDate) = cal.Date
;


insert
into cmpe226a6.REVIEW
(ReviewKey, ReviewId, ReviewComment, Rating)
  select null, r.ReviewId, r.ReviewComment, r.Rating
  from cmpe226operational.review r

on duplicate key update
  ReviewId = r.ReviewId
  , ReviewComment = r.ReviewComment
  , Rating = r.Rating
;


insert
into cmpe226a6.FEEDBACK
(CustomerKey, ProductKey, CalendarKey, ReviewKey)
  select cc.CustomerKey, pp.ProductKey, cal.CalendarKey, rr.ReviewKey
  from cmpe226operational.review r
    join cmpe226a6.PRODUCT pp
      on r.ProductId = pp.ProductId
    join cmpe226a6.REVIEW rr
      on r.ReviewId = rr.ReviewId
    join cmpe226a6.customer cc
      on r.CustomerId = cc.CustomerId
    join cmpe226a6.CALENDAR cal
      on Date(r.ReviewDate) = cal.Date

on duplicate key update
  ProductKey = pp.ProductKey
;


insert
into cmpe226a6.avgrating
(ProductKey, AverageRating)
  select t.ProductKey, t.ar
  from (
         select pp.ProductKey, AVG(r.Rating) ar
         from cmpe226operational.review r
           join cmpe226a6.PRODUCT pp
             on r.ProductId = pp.ProductId
         group
         by pp.ProductKey
       ) t

on duplicate key update
  AverageRating =  t.ar
;

