CREATE TABLE addressablecontact
(
  AddressableContactId INT(11) PRIMARY KEY NOT NULL,
  FirstName VARCHAR(255),
  LastName VARCHAR(255),
  Email VARCHAR(255),
  StreetAddress VARCHAR(255),
  CustomerId INT(11),
  ZipcodeId INT(11),
  CONSTRAINT ADDRESSABLECONTACT_CUSTOMER_CustomerId_fk FOREIGN KEY (CustomerId) REFERENCES customer (CustomerId),
  CONSTRAINT ADDRESSABLECONTACT_ZIPCODE_ZipcodeId_fk FOREIGN KEY (ZipcodeId) REFERENCES zipcode (ZipcodeId)
);
CREATE INDEX ADDRESSABLECONTACT_CUSTOMER_CustomerId_fk ON addressablecontact (CustomerId);
CREATE INDEX ADDRESSABLECONTACT_ZIPCODE_ZipcodeId_fk ON addressablecontact (ZipcodeId);
CREATE TABLE brand
(
  BrandId INT(11) PRIMARY KEY NOT NULL,
  BrandName VARCHAR(255) NOT NULL
);
CREATE UNIQUE INDEX BRAND_BrandName_uindex ON brand (BrandName);
CREATE TABLE category
(
  CategoryId INT(11) PRIMARY KEY NOT NULL,
  CategoryName VARCHAR(255) NOT NULL
);
CREATE UNIQUE INDEX CATEGORY_CategoryName_uindex ON category (CategoryName);
CREATE TABLE city
(
  CityId INT(11) PRIMARY KEY NOT NULL,
  CityName VARCHAR(255) NOT NULL,
  StateId INT(11) NOT NULL,
  CONSTRAINT CITY_STATE_StateId_fk FOREIGN KEY (StateId) REFERENCES state (StateId)
);
CREATE INDEX CITY_STATE_StateId_fk ON city (StateId);
CREATE TABLE contactphone
(
  PhoneNumber VARCHAR(20) NOT NULL,
  AddressableContactId INT(11) NOT NULL,
  CONSTRAINT `PRIMARY` PRIMARY KEY (PhoneNumber, AddressableContactId),
  CONSTRAINT CONTACTPHONE_ADDRESSABLECONTACT_AddressableContactId_fk FOREIGN KEY (AddressableContactId) REFERENCES addressablecontact (AddressableContactId)
);
CREATE INDEX CONTACTPHONE_ADDRESSABLECONTACT_AddressableContactId_fk ON contactphone (AddressableContactId);
CREATE TABLE customer
(
  CustomerId INT(11) PRIMARY KEY NOT NULL,
  UserName VARCHAR(255) NOT NULL,
  PasswordHash VARCHAR(128) NOT NULL
);
CREATE UNIQUE INDEX CUSTOMER_UserName_uindex ON customer (UserName);
CREATE TABLE `order`
(
  OrderId INT(11) PRIMARY KEY NOT NULL,
  OrderStatus VARCHAR(20) NOT NULL,
  OrderDate DATETIME NOT NULL,
  CustomerId INT(11) NOT NULL,
  AddressableContactId INT(11) NOT NULL,
  PaymentId INT(11) NOT NULL,
  CONSTRAINT ORDER_ADDRESSABLECONTACT_AddressableContactId_fk FOREIGN KEY (AddressableContactId) REFERENCES addressablecontact (AddressableContactId),
  CONSTRAINT ORDER_CUSTOMER_CustomerId_fk FOREIGN KEY (CustomerId) REFERENCES customer (CustomerId),
  CONSTRAINT ORDER_PAYMENT_PaymentId_fk FOREIGN KEY (PaymentId) REFERENCES payment (PaymentId)
);
CREATE INDEX ORDER_ADDRESSABLECONTACT_AddressableContactId_fk ON `order` (AddressableContactId);
CREATE INDEX ORDER_CUSTOMER_CustomerId_fk ON `order` (CustomerId);
CREATE INDEX ORDER_PAYMENT_PaymentId_fk ON `order` (PaymentId);
CREATE TABLE orderline
(
  LineNumber INT(11) NOT NULL,
  OrderId INT(11) NOT NULL,
  Quantity MEDIUMINT(9) NOT NULL,
  PriceWhenPurchased DECIMAL(10,2) NOT NULL,
  ProductId INT(11) NOT NULL,
  CONSTRAINT `PRIMARY` PRIMARY KEY (LineNumber, OrderId),
  CONSTRAINT ORDERLINE_ORDER_OrderId_fk FOREIGN KEY (OrderId) REFERENCES `order` (OrderId),
  CONSTRAINT ORDERLINE_PRODUCT_ProductId_fk FOREIGN KEY (ProductId) REFERENCES product (ProductId)
);
CREATE INDEX ORDERLINE_ORDER_OrderId_fk ON orderline (OrderId);
CREATE INDEX ORDERLINE_PRODUCT_ProductId_fk ON orderline (ProductId);
CREATE TABLE payment
(
  PaymentId INT(11) PRIMARY KEY NOT NULL,
  AuthCode VARCHAR(100) NOT NULL,
  AddressableContactId INT(11) NOT NULL,
  CustomerId INT(11) NOT NULL,
  PaymentAmount DECIMAL(10,2) NOT NULL,
  CONSTRAINT PAYMENT_ADDRESSABLECONTACT_AddressableContactId_fk FOREIGN KEY (AddressableContactId) REFERENCES addressablecontact (AddressableContactId),
  CONSTRAINT PAYMENT_CUSTOMER_CustomerId_fk FOREIGN KEY (CustomerId) REFERENCES customer (CustomerId)
);
CREATE INDEX PAYMENT_ADDRESSABLECONTACT_AddressableContactId_fk ON payment (AddressableContactId);
CREATE INDEX PAYMENT_CUSTOMER_CustomerId_fk ON payment (CustomerId);
CREATE TABLE product
(
  ProductId INT(11) PRIMARY KEY NOT NULL,
  ProductName VARCHAR(255) NOT NULL,
  Description TEXT NOT NULL,
  ImageUrl VARCHAR(1024),
  Model VARCHAR(255) NOT NULL,
  Price DECIMAL(10,2) NOT NULL,
  SubCategoryId INT(11),
  BrandId INT(11) NOT NULL,
  CONSTRAINT PRODUCT_BRAND_BrandId_fk FOREIGN KEY (BrandId) REFERENCES brand (BrandId),
  CONSTRAINT PRODUCT_SUBCATEGORY_SubCategoryId_fk FOREIGN KEY (SubCategoryId) REFERENCES subcategory (SubCategoryId)
);
CREATE INDEX PRODUCT_BRAND_BrandId_fk ON product (BrandId);
CREATE INDEX PRODUCT_SUBCATEGORY_SubCategoryId_fk ON product (SubCategoryId);
CREATE TABLE review
(
  ReviewId INT(11) PRIMARY KEY NOT NULL,
  Rating TINYINT(4) NOT NULL,
  ReviewComment TEXT,
  CustomerId INT(11) NOT NULL,
  ProductId INT(11) NOT NULL,
  CONSTRAINT REVIEW_CUSTOMER_CustomerId_fk FOREIGN KEY (ReviewId) REFERENCES customer (CustomerId),
  CONSTRAINT REVIEW_PRODUCT_ProductId_fk FOREIGN KEY (ProductId) REFERENCES product (ProductId)
);
CREATE INDEX REVIEW_PRODUCT_ProductId_fk ON review (ProductId);
CREATE TABLE state
(
  StateId INT(11) PRIMARY KEY NOT NULL,
  StateName VARCHAR(255)
);
CREATE UNIQUE INDEX STATE_StateName_uindex ON state (StateName);
CREATE TABLE subcategory
(
  SubCategoryId INT(11) PRIMARY KEY NOT NULL,
  SubCategoryName VARCHAR(255) NOT NULL,
  CategoryId INT(11) NOT NULL,
  CONSTRAINT SUBCATEGORY_CATEGORY_CategoryId_fk FOREIGN KEY (CategoryId) REFERENCES category (CategoryId)
);
CREATE INDEX SUBCATEGORY_CATEGORY_CategoryId_fk ON subcategory (CategoryId);
CREATE UNIQUE INDEX SUBCATEGORY_SubCategoryName_uindex ON subcategory (SubCategoryName);
CREATE TABLE zipcode
(
  ZipcodeId INT(11) PRIMARY KEY NOT NULL,
  Zipcode CHAR(10) NOT NULL,
  CityId INT(11),
  CONSTRAINT ZIPCODE_CITY_CityId_fk FOREIGN KEY (CityId) REFERENCES city (CityId)
);
CREATE INDEX ZIPCODE_CITY_CityId_fk ON zipcode (CityId);
CREATE UNIQUE INDEX ZIPCODE_Zipcode_uindex ON zipcode (Zipcode);