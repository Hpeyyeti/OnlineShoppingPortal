<?php


class SimulatedOrderGenerator
{
    /** @var PDO */
    private $dbh;

    /** @var DateTimeInterface */
    private $minDate;

    /** @var DateTimeInterface */
    private $maxDate;

    /**
     * @param PDO $dbh
     */
    public function __construct(PDO $dbh)
    {
        $this->dbh = $dbh;
    }

    function generateOrders($numOrders, DateTimeInterface $minDate, DateTimeInterface $maxDate)
    {
        $this->minDate = $minDate;
        $this->maxDate = $maxDate;
        //$this->cleanUpDb();
        return $this->processOrders($numOrders);
    }

    private function getRandomRowsOfUserData($numRows = 1)
    {
        $sql = "select * from simulateduserdata order by rand() limit $numRows";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getRandomRowsOfProductData($numRows = 1)
    {
        $sql = "select * from product order by rand() limit $numRows";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();
        $augmentedRows = [];
        foreach ($stmt->fetchAll() as $row) {
            $row['Quantity'] = mt_rand(1, 3);
            $row['BrandName'] = $this->getBrandName($row['BrandId']);
            $row['CategoryName'] = $this->getCategoryName($row['SubCategoryId']);
            $augmentedRows[] = $row;
        }
        return $augmentedRows;
    }

    private function processOrders($numOrders)
    {
        $rows = $this->getRandomRowsOfUserData($numOrders);
        $orderDataRows = [];

        foreach ($rows as $i => $row) {
            $zipcodeId = $this->getOrCreateZipcodeId($row['City'], $row['State'], $row['Zipcode']);
            $CustomerId = $this->getOrCreateCustomerId($row['UserName'], $row['PasswordHash']);
            $addressableContactId = $this->getOrCreateAddressableContactId($CustomerId, $zipcodeId, $row['FirstName'], $row['LastName'], $row['Email'], $row['StreetAddress']);
            $contactPhone = $this->createContactPhone($row['PhoneNumber'], $addressableContactId);
            $ProductDataRows = $this->getRandomRowsOfProductData(mt_rand(1, 5));
            $OrderTotal = $this->computeOrderTotal($ProductDataRows);
            $PaymentAuthCode = $this->generateRandomPaymentAuthCode();
            $PaymentId = $this->generatePayment($addressableContactId, $PaymentAuthCode, $CustomerId, $OrderTotal);
            $OrderDate = $this->getRandomDate();
            $OrderStatus = 'CHARGED';
            $OrderId = $this->generateOrder($CustomerId, $addressableContactId, $PaymentId, $OrderStatus, $OrderDate);
            $this->generateOrderLines($ProductDataRows, $OrderId);

            $productDataRow = $ProductDataRows[array_rand($ProductDataRows)];
            $reviewComment = $this->generateReviewComment();
            $reviewRating = mt_rand(1, 5);
            $this->generateReview($reviewRating, $reviewComment, $CustomerId, $productDataRow['ProductId'], $OrderDate);

            $r = $row + compact('CustomerId', 'ProductDataRows', 'OrderTotal', 'PaymentId', 'PaymentAuthCode', 'OrderDate', 'OrderStatus', 'OrderId');

            $orderDataRows[] = $r;
        }

        return $orderDataRows;
    }

    private function getBrandName($brandId)
    {
        $sql = "select BrandName from brand where BrandId = ?";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$brandId]);
        return $stmt->fetchColumn(0);
    }

    private function getCategoryName($categoryId)
    {
        $sql = "select SubCategoryName from subcategory where SubCategoryId = ?";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$categoryId]);
        return $stmt->fetchColumn(0);
    }

    private function getRandomDate()
    {
        $daysBetween = $this->minDate->diff($this->maxDate)->days;
        $daysToAdd = mt_rand(0, $daysBetween);
        $dt = new DateTimeImmutable('@' . $this->minDate->getTimestamp());
        return $dt->modify("+$daysToAdd days")->format('Y-m-d H:i:s');
    }

    private function generateOrderLines(array $productDataRows, $orderId)
    {
        $i = 1;
        foreach ($productDataRows as $row) {
            $sql = "insert into orderline (LineNumber, OrderId, Quantity, PriceWhenPurchased, ProductId) values (?, ?, ?, ?, ?)";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([$i, $orderId, $row['Quantity'], $row['Price'], $row['ProductId']]);
            $i++;
        }
    }

    private function computeOrderTotal(array $productDataRows)
    {
        $sum = 0;
        foreach ($productDataRows as $row) {
            $sum += $row['Price'] * $row['Quantity'];
        }
        return $sum;
    }

    private function generateOrder($customerId, $addressableContactId, $paymentId, $orderStatus, $orderDate)
    {
        $sql = "insert into `order` (OrderStatus, OrderDate, CustomerId, AddressableContactId, PaymentId) values (?, ?, ?, ?, ?)";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$orderStatus, $orderDate, $customerId, $addressableContactId, $paymentId]);
        return $this->dbh->lastInsertId();
    }

    private function generatePayment($addressableContactId, $authCode, $customerId, $paymentAmount)
    {
        $sql = "insert into payment (AuthCode, AddressableContactId, CustomerId, PaymentAmount) values (?, ?, ?, ?)";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$authCode, $addressableContactId, $customerId, $paymentAmount]);
        return $this->dbh->lastInsertId();
    }

    private function generateRandomPaymentAuthCode()
    {
        return mt_rand(10000, 1000000);
    }

    private function createContactPhone($phoneNumber, $addressableContactId)
    {
        $sql = "insert ignore into contactphone (PhoneNumber, AddressableContactId) values (?, ?)";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$phoneNumber, $addressableContactId]);

        return compact('phoneNumber', 'addressableContactId');
    }

    private function generateReviewComment()
    {
        $text = '
            Throwing consider dwelling bachelor joy her proposal laughter. Raptures returned disposed one entirely her men ham. By to admire vanity county an mutual as roused. Of an thrown am warmly merely result depart supply. Required honoured trifling eat pleasure man relation. Assurance yet bed was improving furniture man. Distrusts delighted she listening mrs extensive admitting far. 
            
            Do commanded an shameless we disposing do. Indulgence ten remarkably nor are impression out. Power is lived means oh every in we quiet. Remainder provision an in intention. Saw supported too joy promotion engrossed propriety. Me till like it sure no sons. 
            
            Article nor prepare chicken you him now. Shy merits say advice ten before lovers innate add. She cordially behaviour can attempted estimable. Trees delay fancy noise manor do as an small. Felicity now law securing breeding likewise extended and. Roused either who favour why ham. 
            
            Fulfilled direction use continual set him propriety continued. Saw met applauded favourite deficient engrossed concealed and her. Concluded boy perpetual old supposing. Farther related bed and passage comfort civilly. Dashwoods see frankness objection abilities the. As hastened oh produced prospect formerly up am. Placing forming nay looking old married few has. Margaret disposed add screened rendered six say his striking confined. 
            
            Depart do be so he enough talent. Sociable formerly six but handsome. Up do view time they shot. He concluded disposing provision by questions as situation. Its estimating are motionless day sentiments end. Calling an imagine at forbade. At name no an what like spot. Pressed my by do affixed he studied. 
            
            Lose john poor same it case do year we. Full how way even the sigh. Extremely nor furniture fat questions now provision incommode preserved. Our side fail find like now. Discovered travelling for insensible partiality unpleasing impossible she. Sudden up my excuse to suffer ladies though or. Bachelor possible marianne directly confined relation as on he. 
            
            Am no an listening depending up believing. Enough around remove to barton agreed regret in or it. Advantage mr estimable be commanded provision. Year well shot deny shew come now had. Shall downs stand marry taken his for out. Do related mr account brandon an up. Wrong for never ready ham these witty him. Our compass see age uncivil matters weather forbade her minutes. Ready how but truth son new under. 
            
            Inhabit hearing perhaps on ye do no. It maids decay as there he. Smallest on suitable disposed do although blessing he juvenile in. Society or if excited forbade. Here name off yet she long sold easy whom. Differed oh cheerful procured pleasure securing suitable in. Hold rich on an he oh fine. Chapter ability shyness article welcome be do on service. 
            
            Use securing confined his shutters. Delightful as he it acceptance an solicitude discretion reasonably. Carriage we husbands advanced an perceive greatest. Totally dearest expense on demesne ye he. Curiosity excellent commanded in me. Unpleasing impression themselves to at assistance acceptance my or. On consider laughter civility offended oh. 
            
            Not far stuff she think the jokes. Going as by do known noise he wrote round leave. Warmly put branch people narrow see. Winding its waiting yet parlors married own feeling. Marry fruit do spite jokes an times. Whether at it unknown warrant herself winding if. Him same none name sake had post love. An busy feel form hand am up help. Parties it brother amongst an fortune of. Twenty behind wicket why age now itself ten. 
        ';

        $allSentences = preg_split('#\.\s*#', trim($text));
        shuffle($allSentences);
        $sentences = array_slice($allSentences, 0, mt_rand(1, 5));
        return join('. ', array_map('trim', $sentences)) . '.';
    }

    private function generateReview($rating, $comment, $customerId, $productId, $date)
    {
        // Create the record unless it already exists.
        $sql = "insert into review (Rating, ReviewComment, CustomerId, ProductId, ReviewDate) values (?, ?, ?, ?, ?)";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$rating, $comment, (int) $customerId, (int) $productId, $date]);
    }

    private function getOrCreateAddressableContactId($customerId, $zipcodeId, $firstName, $lastName, $email, $streetAddress)
    {
        // Create the record unless it already exists.
        $sql = "insert ignore into addressablecontact (FirstName, LastName, Email, StreetAddress, CustomerId, ZipcodeId) values (?, ?, ?, ?, ?, ?)";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$firstName, $lastName, $email, $streetAddress, $customerId, $zipcodeId]);

        // Get the id of the record.
        $sql = "select AddressableContactId from addressablecontact where FirstName = ? and LastName = ? and Email = ? and StreetAddress = ? and CustomerId = ? and ZipcodeId = ?";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$firstName, $lastName, $email, $streetAddress, $customerId, $zipcodeId]);

        return $stmt->fetchColumn(0);
    }

    private function getOrCreateCustomerId($userName, $passwordHash)
    {
        // Create the record unless it already exists.
        $sql = "insert ignore into CUSTOMER (UserName, PasswordHash) values (?, ?)";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$userName, $passwordHash]);

        // Get the id of the record.
        $sql = "select CustomerId from CUSTOMER where UserName = ?";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$userName]);

        return $stmt->fetchColumn(0);
    }

    private function getOrCreateCityId($cityName, $stateId)
    {
        // Create the record unless it already exists.
        $sql = "insert ignore into city (CityName, StateId) values (?, ?)";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$cityName, $stateId]);

        // Get the id of the record.
        $sql = "select CityId from city where CityName = ? and StateId = ?";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$cityName, $stateId]);
        return $stmt->fetchColumn(0);
    }

    private function getOrCreateStateId($stateName)
    {
        // Create the record unless it already exists.
        $sql = "insert ignore into state (StateName) values (?)";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$stateName]);

        // Get the id of the record.
        $sql = "select StateId from state where StateName = ?";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$stateName]);
        return $stmt->fetchColumn(0);
    }

    private function getOrCreateZipcodeId($cityName, $stateName, $zipcode)
    {
        $stateId = $this->getOrCreateStateId($stateName);
        $cityId = $this->getOrCreateCityId($cityName, $stateId);

        // Create the record unless it already exists.
        $sql = "insert ignore into zipcode (Zipcode, CityId) values (?, ?)";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$zipcode, $cityId]);

        // Get the id of the record.
        $sql = "select ZipcodeId from zipcode where Zipcode = ? and CityId = ?";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$zipcode, $cityId]);
        $zipcodeId = $stmt->fetchColumn(0);

        return $zipcodeId;
    }

    public function cleanUpDb()
    {
        $this->dbh->exec("delete from review");
        $this->dbh->exec("delete from orderline");
        $this->dbh->exec("delete from `order`");
        $this->dbh->exec("delete from payment");
        $this->dbh->exec("delete from contactphone");
        $this->dbh->exec("delete from addressablecontact");
        $this->dbh->exec("delete from customer");

        $this->dbh->exec("delete from zipcode");
        $this->dbh->exec("delete from city");
        $this->dbh->exec("delete from state");
    }

}