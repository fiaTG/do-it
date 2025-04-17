-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: familyboard
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `app`
--

DROP TABLE IF EXISTS `app`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `app` (
  `appID` int(11) NOT NULL AUTO_INCREMENT,
  `appName` varchar(255) NOT NULL,
  `appPfad` varchar(255) NOT NULL,
  `appIcon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`appID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app`
--

LOCK TABLES `app` WRITE;
/*!40000 ALTER TABLE `app` DISABLE KEYS */;
INSERT INTO `app` VALUES (1,'Gallerie','	http://localhost/files/Do-IT/private/apps/gallery.php','fa-solid fa-image'),(2,'Einkaufsliste','http://localhost/files/Do-IT/private/apps/shoppingList.php','fa-solid fa-cart-shopping'),(3,'ToDoListe','http://localhost/files/Do-IT/private/apps/toDoList.php','fa-solid fa-list-check'),(4,'Kalender','http://localhost/files/Do-IT/private/apps/calender.php','fa-solid fa-calendar');
/*!40000 ALTER TABLE `app` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bilder`
--

DROP TABLE IF EXISTS `bilder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bilder` (
  `bilderID` int(11) NOT NULL AUTO_INCREMENT,
  `titel` varchar(255) DEFAULT NULL,
  `bild` mediumblob NOT NULL,
  `uploaded` timestamp NOT NULL DEFAULT current_timestamp(),
  `famID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  PRIMARY KEY (`bilderID`),
  KEY `famID` (`famID`),
  KEY `userID` (`userID`),
  CONSTRAINT `bilder_ibfk_1` FOREIGN KEY (`famID`) REFERENCES `family` (`famID`) ON DELETE CASCADE,
  CONSTRAINT `bilder_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bilder`
--

LOCK TABLES `bilder` WRITE;
/*!40000 ALTER TABLE `bilder` DISABLE KEYS */;
INSERT INTO `bilder` VALUES (2,'Family',_binary 'ÿ\Øÿ\à\0JFIF\0\0\0\0\0\0ÿ\Û\0C\0															\r\r%\Z%))%756\Z*2>-)0;!ÿ\Û\0C	,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,ÿÀ\0\0´\0ù\"\0ÿ\Ä\0\0\0\0\0\0\0\0\0\0\0\0\0\0ÿ\Ä\0D\0\0\0\0!\"1AQa2q‘#¡BR±Á\Ñ\á\ð$SCbr‚’“3£\ñ%5T”\Âÿ\Ä\0\Z\0\0\0\0\0\0\0\0\0\0\0\0ÿ\Ä\0-\0\0\0\0\0\0\0\0!1A\"Qa2‘\ð#qÁ3¡ÿ\Ú\0\0\0?\0#]GS_3º\Õ<®H\õ’Fc¶·t\Í\Z+Xs\ô~m@á²‚J\\§,’®¡ÿ\0‰Â­2ÅºÂ¡Y.Ìƒc{t7ù+\æ\"ŽlŸ0Ìª+%’¾¢\ZjMQ\r3–9D\ð#¦¦m6}Ý…\Ï^Ø¡•\Ôf3Q¥2V‚zu’5\"I<£H\Ê\ÓF„t¸!€\Ø`5a\ÜW!52†£\Äv\ð®†xZ$ŽŠt\Ìh¤Yœ¤\á‰b§†½Dnv$z[Fm˜Bhh©ie‡Ž\òˆb¥Š?\ÂHË³*ŠžR\Ýn\0µúÛŠ7È“.’)s:‡¦†ž\Ë2\Â\ÓK$\Ò+¤H«¹\Ó{€\r\÷ÀŠ\ÊLÒŠ\nGš¨µdZ¥††TT–QN\Ì$Jn¢Ò‘c\Ôþ°\"\ìMq˜øŽtP2M¢PjKKúR8„\r4‰q#Gr5\ì¾\ö>ƒb¥À!K\0M\È€HH\ó\\¾º°~•j*FŠ*\Ú\ZúÄŽ–94J \è\"6 K \ï¦\Ö\Ã\"ƒT’\×N)\Ø-\Z«T;šJ\ôÀMJ\Ä[E¤\nA\í½ú\\–!W\ã¨jº§-JJ‰*\Ú7¦F\n\èl\ÜI‰4Rwcaa…\ÎaMO—J\Ò\ÔÉš%UJ\ÎÁfHfÔ‘G8Õ¦\×\Ö\Z\Ý\É}±b˜0ex¨¢ž\nZ2rB\íÄ–u3$ƒlÀPM½\ìQÁ\Ãj›±t•Ãª¿7\0¢ÿ\0\Ãqp=\Î\rn\æ=n”\é’\nÊŠR\ã\ô„\ájjL“#¼¶¢‹lm\"Þ›Ü’\ß\×kò¸ª“0xXÁ[UL)¼\Êu\nC\rú7·\Ë\å¦\ñ=4YI¦©J¯.\Ô\ëW°¤]¸\în\Ã\â6#¸\ëm\Þ\È\ñ;‘o\ëŒ\ß\×j³†™\æ‚*e¤vY ˆƒ«\öO[_ø\â\âºHª\ñ²²0Ye \÷m†0(\ïúÌª¬}–\öý\ç³h3ª‰(—/’\Ñ²´’ª’“\"ž\äf\èl:_\ä’(”¼²$h¤`«rl\Ø\Û¤\ð<³B’,:x©¸e.\rc\ëü°X«€ÅA090Žy8\õZc¦NÒ•B°\ÒOSb\Ãpv83—C[K4RªÂ”\ò²\äetÙ–R\Ä\õ7± À¹%oçˆª)i*–4©†9R9c@Fn­cýý\ðƒ_\Ì\ê\æ\ç—ø\×7\Ê\óY\â¦\Ë\êu3\Óœ-±»Gœe…x\áE»=Ht€+ø_9Ë²¡ž\ê‘L—Ÿ-8„#¨BY£:U˜Ž‚\Ì5Y_„<?$Uk<--EST*™¿D\ì„d\Üµ†\Ý\Ï\íb–g‘x4\Ì\Ô^P\Ó\ÍVb¬©l­ª4\ÄR$\ã‰YÂ½\Éø@\å-pw\Äv0m\æfdk\ÜLR\Ë\ë!®–µM\Ó\åK4Oš¹*•yÂ»\ÈcJ¤o\ÂyA,û2l·½—zµ\ðWÂ¯$¤|ž›-«Y\rU>_Q\Ã\å“L\ì\òMZ\×i“J^\Ä|6…)2O\rÔŒú\nH\óH(’µ)«jZO,&‰ù\ô—Š.,c@\Ó$†Ã«Š›,´”§0¦\ÊŒu²yS\rD+4\âGŠVÀ±&\ä(\ô\÷\Ç@¨6B\ñ\ñ WYŒN\ËJ\ð‚Ë ±š\ìI\åÛ¹Å™V¹\ÐW™8™g\á\ÓDL.i”´\ñþÔ‹µÁ¹\ôÿ\0”\Ý‚3ºl²‡0†\"-\rVc\\\÷Q\"\rhR&ML¾Þ~›„£\ó³ÁS\Ãf>b}lŠ°\ÓFu€†C¨E–Ä“ª\ØR\ïÌ¯#™AÆ­.Gvk¨ø)\'J\Û\élJ>OIW1F’º®ªšŠ0²D¦—T\Ò3ƒ\ÃÝŠ‹M·xcŠ34Ñz|Ê³†\ÓrdŒ1\Ó\í\õ\Ç55MO\áü²S=4•.yK\Ö\×U,N\ä\é¿Ö›w(\Ëb?\å^§\Ì<Œ\éO{\ÍU˜\ÃQS\'\"À\Ú%u6¡m¸6;\Û	\ÒM7ˆsX§‘©œAOMÀ«$P\Z*8\Ã4pˆ®@\Èým\Ô\0\Õ\â<Ö§)\ÉWÂ±\ÔI4³•Ž\éO\r1\\²\ä¤kÁ‘Ø´»{/·72´””\òPC%u]*C\å\Új\È)o\ZTÌŒÓŽFYyR3¤\\mb5^“WQ\æ´/’Ê‚”Ô¥	‚¢·2\rO\n6hV²›QWg\â=µ`@<\Ä’d§\Éf\ñFaUOSSWAV’©Ò¶\ÍB³§•\ÈEV”\0\Â\à\èß³až\ËÀYÔ”™[“5D\ñQI-d\Ñ-9šzE@‘\Ë+u° ¸¾û·ù\Z6žŽ­¡\rUI”\ô\óµÌ«C®®\÷°\ëüp\ÛChUy¹\ç\Ð‡\õ\æzj³QKEQ\rR´^IHŽž8\"Ò’\"µ\ÉrÀ5\Ëp{\ñV@sJ\ójš\Ùh\ëà£ŠŸ„ÿ\0‹N¯((bX”»¹\0\í‡k\Øý±\Ô\ð\Ôp8ŠC<uûIÊŸ\ïÿ\0†À%}0	\áO’IW˜Í•\åTŒ\ó«²D&cä¦´r\Èücb\÷!›I°\ÜmÅ¿\ö\Å\è\Ãÿ\0\ì¦=Yiro\rP\æ5gˆ´´\òU\× r²5(¨Ñ®\n2\Ö!Y€²ß©\ô\èýº\Ë?ü,\ß\í\ó\Ä\Æ0?Q’(f.\Ç-;-b†ªu™#¤2\ÔW0x\Ý#–œ¹¸W±ø0\ÞÜ¶2O—»\Õ\ÉTüv’„T* ’fF),%¶›þ½™½-Ðˆ©\óJJ\Z\é\æ–	å¤¤H%¡š\Ô\ô¢Wy\å§GRCFIV6\0›Xl$›˜\ë*i\ãZ¦j™©ª\Ú*h\çL¼–\ã\Â9µ¸µ\ÅûúŒd\ö©°`\öŽ¡Ž’ž¾\Zv¢£ý%\nUež8’’f‘\r.»²3i\î\Ý,0^OÐ´R5³\n\è\â«I:=F˜\Ù\É\Ón/°1\ô\å]Ë“/Í ª§Y+\éiG\Å<t\r u\â#\Ü\ÈÅ”–\ÊN¥\'\Ò\àX‚¾	Hã¸«Ëª|½-3N±©iF£4’D\ö<;ª‘¹\ëkXm©(²¬„T\í	ºTMY%//‰ ¤·)ŽFŒ¾‘¬X‚¬\r·ù\÷Ož\Zºušzš¶“/ZdY¤†:€<k)\æ\']\í\Òý\ðR“\'\Ë\0\É\n\Í$²\ÇU$²9‘¤–2J8am…Í…€µ±\Îe•«\Êù$Z\óAÁH8’²Dmx‰pEX\Ûs¤|‹Q®cm5f\\¢Ži\é›†\ÑR¯\n‹¢\ò\émI¿¡\ÜþX·…Y§Í¨¼µqI4‚’\\Æ¶’V)\Z{j\áG£© \ôN\ã|Es›K=$R ™kc¢i¦‹–”3 ™$l\à\ïn\Öß½@\rC¸Füf4º‚ b\ZŠ\ì[{u‹GœŒ†T†$° I\êH8O—\æ\ÐUT¼OOOD’‘CK	a#8\Õ#¢¨‹7\êz‹`\æ3\nEÁW\î\ó\æY¦]›<²¬“G&^²\Â\ÉJ«³\Ãb	¹[–\Þ\Öë±Ÿ,¡\Í!©E’%&^\óR4s@¬\õJm\":Jä¶Ž`ûi¶ûœ0[\Â|\ó\0X6ŠŸ5JŠ¦¯©J„\ZEª¬vRXq(°n‚\÷8!\ã-ŠB¡G;\ÉDš\åHex\Ò\Ä\ëuRB\Øo¾(<=œW\Zœ\Ã6©\ÓS_I\\\Ñÿ\0˜„ºR\"\Ý@\àØ“µ\Ç3±¼+.\ã\ÌV@O3\Ë3ø“$Zªº9áš†BÊ°†I\'Œ¹¼“Ãº–\0\ó½†\Ö2OdY„\ÑC.¹Û†s\ZšŠz…Uq\Æm2\èm‹\0.Tƒclz<°\Å<R\Ã*\êŠTh\ä[‘©X‹\ñZ+\Ë(1KN¨cŒDŒnÎ©`\n†m\÷°-\êw;\â^ˆ¿©?D_Eø\ò\ê¯\Óg•~e*r\Ö\Ê\Úy©ªƒ¾¬\Ì\"\Æ\ÎY\É:$ýaq\×\ë.Ë²h\ä\ñQW:Á®¨¤¯;\öF`Ñ¢%\÷&ÁF­­\×»œøvŸ4’*ª—«šZzÈª)\á†Xã‰¢ŒSÊ²\êM\î\çM\Í\ð“\âüº¦Š,º¹©•Z‚Hh\ß\ËF\"¦e‡E\\I\ÅÊ‹²nNýe=N*z®\"[ªGSR¼@úd-d$µ¹vùŒq—FkO+\ÔT\ÓE–QYM‘©dXŠ\ÇPÁw\n­¤±\ì/m\ñ-L$fUzu\'\Ë‹\ÝÜ¸V*-q{­ü(\Å#iª	ÅŽIa\Ñ)™ƒ\ÕÐ¢‘p\Ö\è}:_¢–\"sY\ÏK]ÁI\å{\É,~jv¨¨Zx–\è†Vd]\Ø\Ü_`=%VeM-m]\ç\ë\ÆqT\ô¹|\ñQK§¨XbŠ8˜\ÝVB–\ÕeT\ê\ÃU\Ê\à9É«\è(\Íe[\Ã\Å$Y\ÐK<’LUÎˆãº\é\åˆ-\×\È!\ñ™Šv®‰)\í<S\âVi\èP\Ú_Uƒs\0Fÿ\0ªV\Ò)bM	\"I5\å0C7™…2Ö¨’¾#”\Ó	a¦«x\Ö^x\n–ku\îV\æÝ¬\ÙG–ft\Ó\ËE\r}FaSM“š\ó\é\"­Ž>1\Ó\ËÔ¦9@T6Ý\ÇM”\çe”T{/£¦¦Jµ¤HVªH\îÀM Ö®— nY€:‰\Õ\ËvK\ãøkèª ª›\ÌVTZZ)S\ÓF…\"‰ª‹\è* †øw¹Ü°y€-v!¬»Ã™¤&‹2¢\ñc#´‹9‡4Wujvcx%]zÁf\ß\âÛ³±O.²Â©™P‘ž,F\Ì\ÌG:Èª¡.B-{\ô\Þ\í\ñ \n\êkU\0q#x£•BJˆ\ë©_LŠ¬5)\Ô\r›kƒ¸\Æpaÿ\0N?ûùbLe\ðcT\ñ\è©#«šŠ ¤•Lµ\ÔÓ¹Šh\ÙØ¬i1\á8`×¾«Ûµ\Ç35\Ê\ÑI\á©\òÅ’«-fŠ:švi\ÒG!$–;©&\öm=-\Ô\ì`LŽ<–=eM4³Q*\Ì\éÇ—+(\ÂXUc.5!;5¾\êŸúDfª¥\Îr\ÈsT\ô•´3»$\ôNeQ§F¦}AJ·kÖµ|	Ž¹=ÿ\0©œ-B™DTQÖ¸§£‚.SqR¨I6¨Ÿ†\ñ\ÉpA»u\Û~¶¥\Èr‰M[™MSBe\á» #¨*iµ;°¾*\äù®YV%xgs1X”UBcš!0@›¡±ž\ëk\í‰\ó\ÎjwŠ*W§5\Æ\Æ§m,\Äj%\Å\ì6±\êÀ{Œh\ö¿¶»eù\Å4±e\ô\ÑŽ¤Z°‡5Ed.Î“­º‚,,_K\à”t5B°Ö™ÝµÉ¤\Ã;°\Ä›\ÂT\Ø-¨¾Ç¨\Â{\çù„\ï\Z\ÖÃŽt1\ÉD‘\Ç+ÈŒX\È‘’Å€;¯l9\Ï+KG[	ä†ª(\Ü\0,Œ€\Û\Û\ë\÷)Œ«\ÌCDl\ãüKŠ\n¹i\ò\ê:yé¡“CKV\Î¬­}Q¢‘a¶\×\ß\Øt\Ã7†|K”\ç\ëV) 4Õ‘˜\ê* p.\âAe™À°\Ø‚\Ý=/\çYe¢¦ Sez––5\n<\íPUÙ›{_\ß~˜c\ð¤³x†LÂ¥©•)2AÆŸ’7–¢rc\0\ÙP“¾\ÛbÁÁ54¶\"«¸\ÏEú`\\¾!\ð\Ü5f†\\Ö‰*Ãˆ\Ú&•AY	¶‚\ß\rûZø£\â¬Í \È3–\Ë\ê-YÁŠ8Š\\:‰§Že$vw\í×¶<}²ÁÄ†Yl\âEF	J\ï¨G9Á*-¹°þOcÌ˜Rß¦}\r\ñ¼$ø¢¶,ŽJj\ævJLÆ¶’‘\Ø¹„ª•\ÓV«o·\Ó\r¾rœ]¶QrO¶ugTZ\ñ?ˆ3Š\Z”\Ë2h`j\Ï\'ç§–~`‘´†4Ž56]D‚I&À}†xk\Ä\Þ(|Ê“.\ñ\nS1i£¢©U…\'Š31–R¤c`A\ï´>/K˜™H™øÔ´\é§‘\ânEùX­šÚ‰8©\Å\Ä\Ïr¹JJ¯DµoÆ™¤†8\Öš0\Ý> 6»Ä¨\Ä\nn\ó=GˆM]\Z¡vž0«Ô“°\Ä5\ÊY´-\\e\ÎÀ@\è	\Ã\Ý\ÈË¸\ÌAæ©€½¯\ÔX’>v\ÃWHXÌ€36\ß\Þø‡\Ì\é>7ˆ\rU(¿â¡³:M\ìOË¶%WF¶–S\ßbM°nt­[˜eùt&zÚ„†;\éR\×,\íû(Š\ì\0VV\ä\Þ,Ê³|ºŽB\ÕKM3¡ŽR\ñ£ cº“\ÊH;_{_~|A+\n\ïÂˆI44\Ô\ê\Ì#’H\ì\Ê\ì=\0½½=°.	ˆ\Ìrš†Ž8\ôWÀ‘I‰ã•¸-²\î	[\÷_\nO5.0’›¢úÀ8d³sz›rƒû±R’ˆÏ›RG’”Êµ\Ñ\Å\ñŠZv5\r€ýU;ûÛ¾\æQ<yŽk :ê¸Š&\äª\ÎÀ…?Aƒþ¢Ys<\îgŒ40\Ñ\nB[MŸ\Ì\È~‹c\ã…\"f\"Ä±?„\ó\õšZü­²\ÈÀ´4T¢ø0\Ä\âXª¦Åµ.5) 5‰6¹f\ð¬¢\\ž”5£šžZºY\áT)™&n#DEcv\0l/o\ÕÁ\Üh*‹\Ù@¹,l\0¹=IÁÀ¸Â›x\Ö7Œ\ÃÇœ\ã1\Ö3:s\ã3t\ñ\ì\â	\èc\õ\ÕqT4\ò¼s\É[¥h•U¸\Íki \íoMÁÁ\Zv¨ƒ/š9Z†i<Îº\ÚC¡J8Leg‰ª‰/}We\Õ{\îF£¼î²²¢·\Ë\Ê\ÚQ´|%šb\ñ¬¡]\â˜Sˆ\ÐÜ‹\0\É\Þ\ç\Ú\nh\ó¹b­ŒÐ´ŽeSÄ‘F’œ,lÒ£:†“~\Û^\ÛxD€M–\èÐ„²Ié£‚¢\òþZv\Í\çWD¦š\×(š_v6º‹\Ø\în1`T\å\Òd¢\Z‡…\çZjšX\Ù\"j“7r­{]‚Ø±6±À|\Æ)\ÚN2JfÝŠ½#j[~d©a¸ l~GV|\ög^$¼3Â±…aG%[(dV¹\ÊF\Ëqk|†ú;qG,²Š\Ó.©†W‘#‚H\í< ¸R\ÂX¸\Ñ)1\ÜlE€#m\ÏcX€²“rw=\ï\ë|È—0\òp\ÉV\ó\Ç\Ñ\ìˆUJFŒ”z­KzX—1\Ê`~Ee42…RV\ÒlÛƒ\Ò\Ûü\ñ\ëi´\Ã\"‡o2¨q³%¦©¨2\Ò\Äx’JÈºÍ‚H\ä\ò³-\Èú`\íFY“d0\×T\àˆ…T¦+1šY¾£\è/\ÑG¥¯\ÛT~Ì©\Ù`š\n’¥Jp§W+¸½ˆ$­\Æ\Ý?¢7ª\ä‰rlbün8eU\Õu@\É6U§­û\â¹t\Ä‰¤e\Þ‰~/\çù›O,~‚| 	±,’$…T†:*$`Å†Á-q\Û!žž¡bš*¹ƒJX\Ð\Üj[\\µ¾~›•\ì¹\Í>YK\Ì )©øa!\å\÷Ë¿Rov7«\Óq_?§‡j,Ö¦^D,¢7<At\Øz\í„Å{mYw#N»Œv JD¢¥4¬|»¯2y	vb_ss\ÓLl/\êo·Ï¶£¼`_M\ÍÅ’\å~—\Þ\ç\03LÖ¿2\Ì$\É2z´¥JX\Ãf•Šn\ë#+ZH>·\æ°5m\Z\â%²&y[1¥\î\ñ=M\\tsD¤\ðL\ð›m¤“q\Í\ïcŠùts\ÔVÊ¬(M\Ä7\Ô\ò:mp{|û\àe=Fm“æ””ÙµSVå™‘xcžRú —`	¾\÷;j\ê[\ÜnÐ•™,*ª’¼º•ŽSn\ê\Z\É\õü\ñ|xSh,|GÍp&Ì†\÷‘gZt\Úl^>1m\Ë9M(:}\ð\ZZ\Êa‡GŠC­U&B®Ì»\ÝJ>[\âlÆ²²®¢c\'QD±ye…L€	Ÿ©7µ€þ8¥I\r5}RD¹š\Z}\Z¹EÂ¶\á\íb\Æ\Ãn‚\çs\'¤\Å‰·N\êpŒ—ÁŽt²·”§–fPx\Ë+>\Ö,£s}¯¾\"j¼²g*Ò£HWp>·\Û\ó9jc£w£4\ëS\0Òˆ\ÖXˆ`,:›`B\Ï\nü\'µ\ÄF½®\Ät\'|eP\Üd\ñ¡\È.5\ËR,j²\Í—VfˆØ°¤\ô±µ°;3Ì³hgz|ªžœ´63\ÏY$¡52\ßD	\rµÆ¢M¯µ¶¾7–\ÔGQK„¾±n%\Þ\Ö±Qr}@\ô\é€4\õžn|\Ñøµ\Û1\Í\"h\ã*\ÅK7[…\"\Â\ß {\r\Íø…U\ßi’Tg5·¤Ì©JVDŒ\÷§šBµ0\Ô4\Üo\ßq–,dR½d’x\ô¤F“]ßR\Æ\äX\0(À<\ÒZh3lºB\Äp ‰gYG\â\é’R\Ém±m­\ß\Û!yx\Å\à¦)I\å\á\È\àt1,aˆ\Í\÷;tÇ£¦Ó¶¢\Â\ñ\÷W¨\ZT¯\ØAÙœUu9\öeK— ’f¨–®¡\å‘P,RJÊ¢\äX3ONƒ\ä\Ù\ÕO†\ó3}\'ž¹â¥–F½…œˆ\äG¶Ž¡\ïíŠ«4§¼Ai+c¨ *\ôL«3´X‚zžk;\ã^ hd¦“ˆ+žI\êt\Â+&V\\\Ä\ö0Ç½€˜n˜\Êû•\öüK¦5l[§©5t\×=6øX°bm~¿\ÓZùX@¢\ñµ\Í\Îý\Ó\Ï\âŸÃ†<¾Žzü\Å ƒ\ÎY_\ËRH\È.Ž\ê7~\çp\ä,\å#lÆ©¨\êiM=bÊ€“\Û\âa¯{Ž¶\î:tÄƒ\ä\îg({	WJ\ài’\ä°[C}A\Ä\Ú\ÒúC-úZ\â\÷\Â\åD\õF\íj\æÀ\"’Ac{a‰‘\äÙ•U”\Ô^ûoq†\Zƒ\äE¨{®¢%¾½d²’›K\õÄ¦¾rWDq¶\æ\ã˜¼\ß\Ó\ÐÎ¨K\Z\Å4\Ì#-iY¹¯¨;l/Ž¼\õ\íŸûN(2)\óK\Ë\ò\ÚiÁ”‰¢…\ê©s>0NZ\ôš.C<Rb¤_m·_U\ðnž1\Õ*€\ÜWv\Z.Y˜If\'sü±\ÓFúD2((	f[\Ø:µý­ü1\Ü\n\äEu	oUÁ\Ú\àslgcZyzn\Z4¨Ž\à5\Ë\èb…—I\Ó}ºmOE]Á\Ì]Â‚dµŒ—Q¤7½¿~$€¡€±\Zw½$|Tªu†(\Þ\×+\Ã\0\ÙZ6,\Út’\Û\í\òÁ@X\òe(	`ª\è]*hU‹H²]†›\0-ˆ£\ZD…O\Ç#^\ãn^˜UQ=%mS\Ò3 ‘ U…]Œl\ånÑ’R\ç¾\Ø;\n\ÉÂƒˆ\0¢ r µ¾·\Ç\Ñi\Øl\Ú<He\ÆV‰\ó\æ/”\ÓCS=zÁ$p\ç”\Å\ÌÉ±Ñª1¯\Ð\õ\í*ž—:\ñ>i[Q•\Ò\Ô<)ùw\ß\ð\é\Ð—–cr\Æ\Ûn-½K0É©\ó§£J‰dZzZ©gx\ÑQ–{€Š¯¬abz`­._GG0@G\Ô3–bŠI\n\Ä\õ¶\'¸\í=@”¼ù‰S\n\ì·-›Dp<\Ô\ô·S26¾*‹\"•&\Æ\Ì@\0Œ\ð\\úaÌ£•™ªZX\'œ¿\Æ\ÒJ¬\Íp{\Þ\à\á\÷7\ËMlrPY\×K€,]F\â\ã\Ôv=þ˜O®\Ê\çÊ³I«¡G4ÙŒQM\Z\Å&–\ã\éVx\\0±»c\í¼4\èq\ä\ç©}KŒØ¸<\Ëy\ætrº#:\é3\Ê\Æ*q{€\ÝK\ÛÛ·¿\Ë^µ°\çZ©\ÖI|\í4\ÄKrI*J¿\Ì“×¯L7Á\á\\ªºH¤\Í8Õ•\"[T®°Dvm0F¶\0v\õ\÷¾7ú‹)†zj\Â	e\rv;\ôÒ­qnƒS”\ä<u—\Âh\÷üO¡dË£]qÕ™ç®8¯ ê¨„¡6\Z¬H\Øt\é‚ù\\\ï,i«\Ð¸¾³Lº¶ç¬©™UU\Ì\çT¤Ç¥­¨\ö\íƒy\ÆD]Dj\ßk\ö¾\Ø\ô	j,¿S\Ãÿ\0)`™+\Ì;43L¼5™R-.¥Jopw2¨\íl\ÑC¨”³¶¢\îE€Ðª,\ß3\÷Á<\ÒIV†±` M$kd›X»…6>¶¾.ÁMO\r4të——QO\Âg‘X\Òj-«\ó¿\Û\r­Ô6@QF\ê\î/\áº&\Ö`ew!/¡\óü2Jjµ“)ZS!y=I\ÔH\Z-R/\Ò\Ûüý\ê\Ùx\òÍ¨p\Ú0—v,\ÂÀ_K§\é‚\ÞWzJ¨Yl\ôÙ•m4F\ß\î\ÙÃ­µz\ß* Jª–Ê£…ž	X4µ7u\Ö.­\ÂQ°\ß{c\ÄÈªO©\Ö\ãs\èt›°©\Å\Ýu9¨¤U,­§I“„®úl4YT`·\õú\ã\Î)*\ó\Z?Fb™\ãZùø34`0`\×\n\Ö`E\×b\r½}q\ë\ë\\µTU‚@‚t\Ó«º!° \Ä^ÿ\0#…\Ú_\r4•\õÙ–\Ô\Ð4d$(#Q±O\Ïn\ßgr»/\Ùý\â\á\ÆQË±®¿\ò*gÔºª\è(\é˜5MaŽ%v$³\Ê\Î\Å\äv\ôQ¹ûa“ƒ5t©S)~\ÄO*•\æ]\÷\ö¿\\>¡§\ÑZ\ã[\n±²\ÂDC¦8c*ŽýI\êI¶\Õs\Z˜Î§x\ÔÈ·\ía{a\ô¹›@§£\Ü:¬	ª\Ä\Ì;~\Ñ!\ÖV\Ír\éP‘#\Ô,.<Š\Î\ß`m\ô\Å\ì\ê“0O)WQ!“+¤jB\ÙWN\ì@[b\æ[C<\Õ\â¡\âŒÒ3#Ýš@\0\ZGµ\ðW1 š¢¦‹Lfd\áY^@·þ\ï‡mBK\Ï2ø– (8þˆ\Ã5-r­E<dWf\nÁ¡i%4‚A+›…\"\Ä}º\ö9UÂ\Ä\Ù¦£T\ä\Ó\Å2*:\Æ\ñ¿›~›\0&ý\ð©G˜f\ÙfoS\r‰¨¬Zc1,\ÜI\Ü5dV\ÛXýS\ï†<–\ó,\öYYmJ\õm3°2I(O1u#\ö­Œ›M™\è†\õh\î;\Ô\Õ\Å\ñu\á£„\èP\Ü5­¸\÷Ä©ªD¥+ ‘E\ä\Õr\ãM\î,?¿¦\nzr%‚¢$’D©Y¦£†ý`· t\Ü\r¿…\Ó\Ä%”±U²Æ‹\Ü1\öM¿<fe\ç°d4\ÒiR^R²ª6ÁA\Ôn}\Æ&Œ¸\nº…€\Ö;X\î6À¸Ù‘i\Òv•ŠXË¬…(.™A¹\ô=qgˆ‰\å\âi,dh[•fv:•AÏ¶$w	Áü\ËF\ô–b5,4û\÷ûc3/ú#ûÿ\0§§¬‚	%S$:\ÍÕ‚;\ñ	–\Ûtúÿ\0Jÿ\0¤SÕ¿\ò¾( ‘\Ô> \Ö:q\Ã\æU:®Üª@\ß~—\Ç\rF–:Eˆ\0.Á\é\ñfý³\ô¶8%š\ä³·s|z_–T\ïZR—+\ÕF³:K¹Š\Ä\r\÷°\ÞØ¬ùU\0Ó½KJ²7\r™£¸XJ\ÚÁ\Üo¾\n•?´\ß\÷s$bM\"Ff¶\öf$m„\ZZ\óª>\"}j<3!P\ÑOJ \n¥F‘p;_\âHØ–Ž\î\0b…ˆbz[l\Ï\é„N\Í\n‚§°\õW!Žûv\ß\0`\ðý\Z¬T\Ó\×S±˜U2S\ÕÌ\È\á\õ\ó\Ä\×]$\õbØ›a \Ëe\÷*‘\ñCÁM¡f},û\é±/p};ZšY$\nŒú•Y™d]&\×\0c=\ËrÙ²ü\Æ9\Å}t\Ñ8–3UA’ \òWJß½\ð\éD£\Ì\È\ÌJ³B\ÛI\"\Ç~¢þ\ð}RrWˆ!\é–\ó\n\0±\é¿\Ó\ó\Å\\\Âji\÷\n^®-\öÖº\Û\ÔýŒJ\Èm\ñÔŽ¿1Š™“´4s\öw Bz)\ÒM¾W\Å\É\âEop©¬¾N$\Ò:\ÃH‘MÀ\Ý\Ü\ê¸?!ù\âü\ñ\Ã(\Ò\ê{\õû\ð#š9\âÌ‚[M=|\ôš\ê`TSù\ß\Þ7\ê]\îzµ¾øÎ½J\å$¹\"&x\ó/§9]$«\Z„§ªX\ì»Yea\õ8]É¢c*[e\0\ö7n0w\Ç²\ÅGKDG-eEÜ¶ü\é6\ä²\àV[±Gÿ\0‡f=Múc\Ôü5}\ì\ßSÀüa¯¯›–³8j*)$†ž&•\äx“HQ*ûnH\\X\ëi©©\õCÅ¬§¥E\Î\Â.:E @,w\÷ú`\Í$J\ïKHœ®9\Ã¶˜¤V$\Û\Ô\í\õ\öÁ\ó\r9!\Ú(\ËlM\ÕO\ç?>C«S¯\Ù\Ñ\á\Z~\Ù<\ÄO½~S–\ç\ÐU\Å,t\Ï, \Ë`&”ÀAxœ¤¡\ÇE…,‚?4\óE,†T¦~¥\ã\å\â \Üû|½pÑ™¬\"O/`EL2\Éb.\0#uù‹`.G”\ZŠg¨P\Új\\1b­\Êv­¶\ë\Þþ\ØËB\ÔÝˆªûÏ™\ÌÐŒÇ†Y„^Igy\\;\ßYM%\ÆÄ©;ÿ\0w5•Î²\Z˜\Ôü<6\ë\Ì9|”µS\å¬BCW\Z¾´*\è\Æa¦ûu½\öÅŒ¬\Ï\r}LÑ’\ð!%”›\ð\\‹‹ø†pÀ\Í\î\ÆÑ•¬v;ƒ±ûzaJº\ëIZŒwgSÿ\0M\ð\Î\ï$Q\Ë<\Ü!J]Š\ê\ö\0\r\÷=\Ï	ùµlb\n²\Ä•&:Ss©\Ô\ØbŒ7:\î\âa!q»7¦²i\Û\ËÁ\ècC\÷Á\ç”3Gcg¶¾\Ç¹8\"ž˜¢Áú(Á\í!T9³\Ü/r{o¦\Ê=\Ó\ã°\ZX‹\rc7\ñ5Q°|µ³jø´\õ5,V– ;\\4€–|—\ÔI—\æÕˆÊ’IP‘F…n)Ð±\n\Ã}\ÉÀ\Êj+3\åq\È®|¦®¡O@\Óq\éª\÷\ÚÄ€/\ï‡ü††J¦‚“EAS5B¬VW\0²’»m°úc\ç±S>³Z@Ã¸1!y¦ŒË‹\è\ÔP°@’G\ç\ò\÷\Æ\ãŽjšººR³D¶Ö“pYTJX›—\îz\r¿~ªL±\ÔK¦Bg0#)±U—2ý\åƒP-\Z²™$šú¶‘´ %µ‹k\ö?,f\\%Á®\Ä}IPÀžŒP\Ì\ò\ÌÒ’QZ\í°¹G¹‰U¬¶¹8±\á\'e-	§%Õ‘\õ0+\Ëx\õ¾þÿ\0“q4ÅÞž6-pÚœÀ‹‚-ŠE–‚tp¥´\ì®\ë°\Õaª;7sß¾,q1ŽD\ÄB_\'fooO6h¨)€f³°$Ý™M¯¹7¹\ël\ãŸ\Ûÿ\0\Ül<T\ä™l\ò\Êü\Â:€T³™\0°\é°\íŠÿ\0\ì\æQþ‡þ\ëÿ\0,]R‡FI–\Í\Üf\Ô=?<a±\é\Ø\\úoˆƒj\n@quVÐ£\rC\õ•·ú[\Þ\÷Û˜ms{^Ø¤3¥k€ß«¸¸\ö\ÆÍºýo×®9\Õ-\í`7/c·_Lh¹oa¨\\ü\ÇN€³\Â\ZU‹”¤\Ó\×{;¸¹¶\Ò\É3RS\Ç¦¥Œ\\jH\Í\È,\Ê7Û¢º_~˜7\âºj¤5t\Ì<\Å5;X,È\ä\×sµÍ{u\écjzY–%šF–yR2Dj\ò±r·<\Ûtúc)1&nÜ­@\ìH2úO7˜Ä…¥•bY^yÜ+¶Km;“½‡n¸-IO4|Cw§“…)\õRt\ß\÷\0\É+keŸ^sþ^\Z\ÄH$\n\Æg	¢\Äv\'©\Ü\\\à\Ì\Õ24±\Ë$fP\ËÂ&„{\0Hk›\è¥°®G1\ñ_ \ôc6 ,@¹\ê~]±G1°€³\éjg³w!\Æ.\n°\ÏOP6FÐ¸a\ëf*v\éŽ*\ózZ˜\á†:z¸ø’Æ\ê@1Ey\år{zb\ç\"•4dW\0ˆc(¢†‚‚ž\í®yX\äšw3H\í\ó$ÿ\0cT\ía¶\Ø\æ;h_d\\dŸ¹ }\Î\0ˆ{‰\Þ+\ÈS2“$—[,TÕŒµZX‚ig[5®¥Qÿ\0]ûb¼!“\Ñê–‘\ë\"Ò€\ó\r$aC£›,€Ø›[¯B~Õ°qi\Ù7üH¥Ao\Úø\×\ó•\õS\r®dTRF9š\"r¨f\0\É2\ÅVž®P*A\0k\ÞB¿˜Ác¶\Ç2¨€§­»\ÕI˜\n¸¿*Žj\ê \à\â4 Jf\ó³Y£”PßžJ:É‡ü«$H?<\ËQ†…PYE4 mm‚\ÓÙ¨U\Î\ògap\Ùel-3¤¿\Ï\ò\É/CHHúc\ébF\rû\Ì\æ\ÓS*f\ÔÅš*”¸xŠn\êd]¿3|\n¢cgVv\0D5“°P@n¿M\ðÁ˜`šÞ‰o¤ŠwÂ¥i‘3¯$€²\Í3\èB8’É©”+\Ù\0_¯\Ï\ÚY\0V[\rº—³\ZÙ«\ZjTgƒ\n\rŒ¯Ó‹\'¢Ž\ß\Ì\ì?Ê¼¦_I3’\ó,\Òù–ø‘\ê°\ö\ZM¿®\é)!¢Œ±T3\És3Ž\äþ ?²;\\\Ï\ãeuE\ô˜œ\r·<P¿\Ä\ãNˆlÌ®{¹‡\ñ±¯U\òq\ÓS\ð\æ‰ù‚Ž¤•\ìm{þX),´´\à\Zš¨\"\Û\áyˆ\ÞÊ‚\íùb\ÅS‘\ÏGH\íCNÿ\0ƒ»Æº¶P7·|_¦Ë²šv<\n:H\Ø‰b]sU\õ	|ƒ<lz<”(Šƒ\ò}2V\ÍP”²ªµ<—©–3¶y\èEaª\Ö\íÓ¦\ËZØŒÔ K\ô»‹t\0ý\ñ\ç\æQ·T\õ0\â\ô“m\Ü_Ì€’¼­ºMJ€ê©¿\çƒ[£\â\ßn¶À¦\\\ÊB,s\Èÿ\0>\ð\Ç\ç‚Z¬½ûmž2`©¾LÛª?¥~\è\Ø‡N»Œa\Ö\õÆ‰:zÜ®\÷ùc@\Ô[{ß·®\ÖÆ‰Še\\’{~˜\ÍQ{Ý3‹)\ZE\ís\Ú\Þ\ç\Z\Ö}cû\àÁ\"¡z8i\Ö*O0 \áxüg‘/c³L5i¿~¦\ê_­À¶þ¢\æ\Ø\ärƒuV[ŽA\ÇTÆ‹!{\0;\Z¬pw$\ó\ÄÀ¡P¹ ,w` u»nIúc–\ÐYlAn‹\Ä7ûo[YJªq¨;\í\ô\"ø\Ùp\rˆ\nX\ì6\æ\õ\éƒ\äÝƒF\Æ2¬’!\óX­¯a¶¤¹‹”\ïkƒ\ß\ç†Vp¿\r]FÀjmØ u>¸\\uTŽm]ˆnÀ‹½þ\ñ,‰\\qW\"‘c\Í|O‹f‰\ï\ê\"\Ô	¿®\ã»¬.\ë}HXu\Â~MQ«>—C\ÕIRlGû½\÷@p\á+F±m·\×[¹·¡p7\ãJDnXµü¸\'J‹:®/}‰\ö\öÅ©eD¨¤iJ“OM[Z‹\éUH?Sž+À¡\êj_kD\æ5\ZF•\Ó\ñ\ó;œZI²®2y\Õm\"ZU2”&¢/w2:Bý6®\0Tc’›qÖ‘Ì”\Ô\ÏûQF\Ûü±Ü·Ò¿\óEESCR„P\ÏOQdF¾ZE}!Ti\Ût\é‰%Ž1¾\í{X\ß\Z‡Svf\ç6ìºœg\àF±›]fdú#ü°j¡d\à¿á¸²\ë\Ü‚IÀ)´K˜Q\ÄYZª}¬\0`„‚TŸCŽ\"\ÅNÃ™s¯@:Š™	ÿ\0¨+bü\ÚL2\í\ÑþXYÊ«\ëj \nUtLeS¶‰c{·\Êÿ\0l0;\Þ	=\â#\î01h–\Î+!‹™\Ä`U\ä3X°Yªc`:b\ro–e‘ŸÑ´¬l\Ô6\î\\`^r\Åc£kmÄœ¸\ZIŽ2?\öpj„ ¤SþœZ¿\æÒ§^\ò`oú”y{¤q”€Æ¦Ã˜³Hª [ÜŒ\0¢–†|û4ªšzaX‹\r*’5tF\áA;\\ýp\ÓZˆ\Ô\Êú´”$–…°¹c\ì:œy\Ý\"¤R\ær°çž®i[PÝµ¶¡p}­‰d\ÉD\Z\ê[-\Èy©\è2†¬W”‹ÜC\Öø›\Ç\ÅË«cK“\åì·½\Ò\Ò\öùcÏ³\\û6\Ë\ä¥J	\êc\ÌÑ¸‚Va§m\ÓsL\n—?Î³ (k\ó*™L.’‹­\Ð1h\Ô\õ8–\ä>\Þ\êz_‡\çYr\èz\"\Û}¬\ç\×U@¨\'S]£\Ä\ò‹1þ\ö\Âßƒ«©«(&0I$‰D‘—V­ATŸŒ“\ßd¦mE`l¶µ\ô\Ün\ÐO\áz\ì¯¾6Å™\"ø…S\ã_¿,c\Ì Z¹›u„jµ\írª,/\îH\ÄF¦’ZŠˆ!MLO\" ¿oˆ\âUV[WKWUˆ­&½2ü-`\Ü\Út•\Øt;\á¨J\"\î?R½”´Í¨3\è¬A±v\'·®\Í==,z\ç™b@-rw&À\ÙTIù\Ë‚gzœ\Û-c¨	\nJ\Ó\Æ¸æ”‹žm\Íýl\r\ñŽi—š|ªZZ\ê)\ôOV)\ê!”\Ñ%\á±\"\ÅHú\ã9s‹ùƒRÁœ‘\Z©\ë(ª¢\ó\ÒjQŒ—\n°#g\r½ý1+•^\ñX†]ºúÜœy=>m–\É05\Í:\Ò^ž–@²1;ù/\Û±ø¦\ÑczyP¼jdGV‹ \ÕÄ·7\÷I\áÖ«\ê\ñ1\îŒ\"\ìt\r6>\ãs\Ö\ö\Ïp\Ñ?\í?\Ï)ªa«‚\nˆˆ\È\ÍSxd[‚¶BEý¯‰¿\Ë~\Ó\ç\ç\à‚,Fw\éxh¡ i¦‘\é\êFªy	icQ¡ã”¯VˆØƒ\Ü_º\ósS˜\ÅYG•²8‰+\ó$¨’\ä3\ÇMû\0¤-‡·Sl$\nµe\àK©D,\íN¨†¥\ÙVC\Ìnl~•\Ï=#A–¡‘\ÑqQø†Gi;Ÿ_\Ü<­$x{QÚ»4Zg†´\ëq¨„!\åH­±\'e¿Wcµ\Îû^ºg4\Ó\Ø\ê	*^\áT® ÊŒ¹o¸\î/mÀÔ£5UTHš‰’¦}\É†š;¡\î@\'Ø‘\÷\Û\ê˜]Šä—¼ªtµû\ë\rÿ\0\òøq¬w\äF\ä\ó\ó,Ò•¨\Ñc\ZIê §4\ÐI©ÀiB¼¤¡]†\ÅNÿ\0/Ù§Y³	Á›L$Hj1X…È¦§‰\É?¬\Í\ö=B´u«£=UÁ«2s¼ŠŸŸ\÷\Ó\ro¤\ëT[)ø˜b~»\ãV,§/¸‰|\\ÙŠ\Ô1¼y\â~‚\Ô\æc¤¯\n\ÐÑ…Û©6\Ã h;–±,\Äu n\Ç~\Ã\é£CšJxj\çÈ±\Zµ\r\ÄúWu\ß\×\Z\Í\\\ÅO8I/\Z&§T†(\á¦A-\Ò\âBÅ¯\ò½ý}û›S\ôÈ²\í$š\É‹L\à\ßS!»]0;2H\ê8®Ê¥Ô¢\í¡@±\ö\Û\ÕI]SSÃ’At\ì\Î\ìr¡²¤·§\Ûpp/\Í3e4QüR4&6vß‘n‰·©½\ð—!\æ†)o¤(%[`¥u\\¾\"\È(¡Ž–\n²TMŽ¶c\ÎI\ß\å‰\ó2Ô™v¨‚ß‚±\ê\Ü\Ø&\äb\Z9\äƒ- “†xžV8Bûü#lJ§©\í\à:j¹+*\ó|¦6<\Zšª&‰$‘HºˆV\'\Õ\÷\ÃE\'\èü¦|·Š\Â\Zzt3I¥…\ÙYT•AO\\%øo‰UœW!‘Œ¶·\Å4‡¯~Ç¾\Z³K3s–8n‰D}±­,-\Ï›sFe\ác*\æ)Y—™xR\ÇÆŽ@daÐ£\"\ó\íî¸ü]\áVŠh“1\×\"«!H\éª\É,@La<#øq?úq•\Ø\í,\Í#Z\ä‡f{}1\Çvy:£«\ñkdFd\ä_W\îÂœ\ì\r5.d\Æ1g^#\ð\ÍlYJG]­\ÅY¼b\õx´–XXt·_–\×Ã•.m’INü<\Ç/dB—\"¦$\Ëpƒ£¦\ó>MÀ7y\r\É\"Í§¯\Ó3ˆ\")I0qNž‚\ö\"\çs7U\'t\ÜL\õ|\ß7\ð\ÒeuOS™P\ð#A+¬S\Ç1“IAM\Í~€wÂ—ž¥\Í&¨ª¥Žd†Eˆ¨Ÿ@‘ÈŒ):c$\Øms…\ßÓ¬y8\àYQd¦×¤Z\Äk6?aŽ|=V\É*)ø&ŠŸ@#£´H<Mœ²\Í\ÐcÉ¶üJ™\Ò\Î(©…DzflÈ«µ¹c,-q\ìGO—ÛºjH¥«\ãy¥g\ô,z\á\Å\ñUdÕ„FRd§­i !¯\õû\à6_\"¥ˆ<*6‘©X\Ë_\n¼ˆ]v\ä?p‡ø{W›;£:W@¥•.\ßY¢vEÃ´\ëÎ¼(\Ì§\\\ö˜=\ÍÁjX\æ\n;o ý±\ã¾¬j,û,^+Ž>ºII°\0\É\ð{ˆ/\ßÐÁUQ”\Ë&tUb©4\Ýt¸†Xˆ>\Öb\ßPZW\Ä\óH\Úh\Åÿ\0S;QžZšº\Z.\ÑVi€$†\ë\×|IP±R\Ð8h\é\á—\ÑE€Ÿü@C“NÐ,µ9|ŠV\è‹7[nûzcšam”G2¢¦dÛ±#q\ôþÁª²™\êh€¦>gš\ç\Õ5µj—p“QC+„\"\ÎÍ«•µ-\ê-€j(-\Z±\'}€¸°\õ\ö\Ã7Šh\ÐI–1Œ³\ÉM4EÙƒ@ú…ÁØ\í€\ðÀ‹\"˜•‚‹°1þ!\âi\ÅI\é\é†LChn¦g\Ón}\Ä\ðL\å%ŽVKÆ )*U#‘\É\ÔI#›kþx¹Ä£‹ð£%m#HI Ø›­\íëŠ©Q7ce¥x•ˆ.HVÿ\0›H7¿\Ó´qE#%\Ý\Å\ã\×\Ì\"6\0\Äû\âŒ)!¦¸Ž6£&oV”æ7šI,t\ÙY·%E±œ\õfû\å€A\êK•V[+¼\Ír	ø­\Û\ï‰\Ï~\Úÿ\0\ä|?2[a&¥ª©¬†5•\Ö™©\ô9B\æ\á#A¯¥Ù·þ²d–\Â\\ag,Q®¦C§\à;}ý¯\Úø£–½CV\Â\ÒB¤¤r—ºHª:ccsk>¸ŽYåž¡“\ÌÀ\îbT(\ÜÄ\Z£+\Íq`¯\È\Þ\Ø\Äp\îjYÞ™c@B\ô\õ	>½z8@ŸÇ•bY\0ø—Y\Ü\ÚúO]½\ík•H\ÜNi!•¹{±\ôþ\öDƒ<\ÍR•)i2<.»È­$§©\è>~ØŠj¸[‡\å\Ì\Ò^I!\ÖG’žCº£²HØ…a\éb®Ì¸H=q(¸\Ùh‘ÄžI¥Ž¢Ž C!h™]\Â¬¡\ÈmÐŽ»{\ã\Ð\ä}*\æFPÀ(7¿Ë¾<\ÆJ©$–œJ„\Ë;\Ä$bŒÁ‘X^2,>\"{{\ò\Üúˆ²þ_‰pSt$›\ìw7Ç¡H.«¶\rˆ8Ì’9‰+\â2\Ýcj“§_¹\ëÿ\0\Î\"\ÍÊŸ\"ŒÀk«Š\Ú\È(¬ýþXXÏ³AI\ÒÌ²¹\òÂ•Œ5¡‘Pt²\ö\0ú\öm†<\Ú.%W‡fOý3 a{¨c	\Ðl}w¶«\Z\îJ\'™!\Ê\ni\r\ægŠ2£«¬w•=m°þ\ÎA\'™Š34<)\ã*†»>’[S*‚w\í¹Å¯\Z\Ë©£¥gh\ÑiZRV\à™¬6{[\n­X\îœ× ’\à©6%,\0²X\Ûë¶ù\ñ\0wþ©è™µ§\Ê%(whÁB7\Þ\àƒû±¬\ÒX¨¨\æu´\ð±_[Fž¿o¾\Ñ\Í%o„\êF¢g¥ˆ\Ä\ÄÈÔƒ–+øÖ¤\ÇKMH\ÍR\ÚÊ­\îcK\ô½¾\ØQ\Ì\Ö[\Ú[\ês\àˆ8‰™V(:¢žz\ß`®\Ä~xa\ÍTÙ„–\Ù#””p’‡“ùÒ”uP¼P\ÎRd©§Ô !Œ\ÊÜ—\îÿ\0v.øžA\ri%1YL e\èQ¶/P\Ü+‹{_\Z€\ö\Ï*C\á_þ\Ó<mm\\F\×klHm¶:\Î])ü/Y#=J„±\ëÄž`\rþ[\â\æO—Rå´²SC)’a\ÏUk”YTM{-Å·Û¾\0ø¶f_øz;Ùª¥2°¸7!n£n­Œ\Ûy\ænCý/\ñxf¡æšŽ±‚¦A\ÖÁ\îE\Í\í\×\Û¼`\æ\n¬¥U¸‹V\åI\Ò	Œ¥·3…\ßÉ¢¦¡\Ú\ÖO/(¹µ\Ú7&\ÂýÈ¾<gw\Îü?\Ý\r\Ò\\\ì-#É¨›ú|va\Æ¤?ža\Å>\æ725µ\Ë·\×\n\ò¼K\Õ/\á¼qÒ²0Ø­™\0#¾=©S\á\ßD¤j«ZiH\òEN==\Øa \æ@\å³\ÐIR#x\Ë\Æ2>=^\Û\à¨\ö\óBO\ñ=\ål\×-—@·š¥•\r­³I\Ø\\Zøz©£ŒKI¹a­\ÕH\õ\é|2xje\Êrù™d?\å\áÕ¡o¾›ƒ\ö¶xÿ\0CF\ô\ÑE\'4„\ÔRÊ¤/–d‘Y\â\ÐÀ“c¿^\í…E®®Ctÿ\0QFcŒ\Â\î…fŽA\Ì\\-Œd2\Ú\×\ï\í·¾=\ß&x³©j`\Ôaž–: \ä2\ØHEƒú\Ýobz{\ã\Â\nF£bI\êA·Rn¶\ï}\ö=·À:Ÿ\Ãt\r`”\ò\ívfF\ò\å£Ö›\ô\"×·¦4bzLY\Ñ?¼þ#\Õ$t\Æ]+-q	¬\ó;Ek*=\ê\õ\Å?V4™|Ô¥†ªi\åP/r#—\ñ–ÿ\0s€ÿ\0\âMP©Î©©Q‚QnW\\\î\Ò·MùoŠþ	žD¨®€n¾Y%]\÷$\ÐE¾£\Ï\îšt¯´\×\Ì\ï\Å2Ô‰\ò\ñ\r•¢j\Ù/©U´¹T \ÐZ\÷?\Ø\r­?\nDˆ±U®\nžU:€\"\÷7ÿ\0\ç}ˆø¢C\ç\âPŠXS\ëI²ž4„m\Ó\0x©]D,¶Ö°$\õ>—\Äh2m\".]¬H¯2ì‘‡ŽgŠ6\Ð9\"M`GW\0º|X $\Ä#\×{¶Cn»vùü\ñ\'\nb\ãKmZÈ¹\Ó\Ò×²ûtþ¸#t½¾¢\×6½ºþX¢ûWiŠ\0(Pü\Ê\Å\êb<Ê¤¨\Ó`,Ë¾\÷°þ8\Ï57\ìÿ\0|Zx”\áo¶\Ý\í\íˆ\ô¯\í·\ô\Âm\'\nÂ“\×G¦”SÌ²\0²\Ä^Ml…—@f\"\ìÃ­·\Ø[\ÖÂ2J\ÒDcP\ÑÆ€ ‘\ÙBE-Ø’Þ›5\í‚QR¬.t˜\Å)O\Ä\'[…\n\ÚK\Þ\Â\æÇ¦\÷ûJÓ¤Z\Ã”i…Eƒ§J¨;\ö°\÷\Æu¥±Ue«iu,\\	\Äj\n\ÚÜ®kC\\ž—\í\Òû\ßr0ù	%–¤M\Z<B6˜qT\\ %$\í½»v=a¼\ì\ò\r‹£<EN†$ŽhÁ=®E¾˜\êJ^Ê 3¨º·A»‘\Û\å\÷\ÅNFq[¦“•\ò-ú\æ§Ut\É*(ˆD\ð\Æ\ê+šJw\Ò\æ8\î\áŠ\õ\è\×#TUfUt\Ò\Å+T\n ‰q\ÓRˆ\":T‰\nJu±ýaú\Ñ%\rT,†\Ãq¥¹±¿U?3ùL\ÝÃ†—‘ù¥1¼1Ð²%‚‚\0n¶¿]\Ëb\É\é\r¢Œ‚\ì\ÆEÑ¨²f*\ñƒœù‰øn¬\Ä\ÛZÀ‘µ\ôŸ¦\÷Ç£¹§ž‘2\ê]U•4Mc\Åc{ú›×¿^\ØUŽ‚EP&ŸmD‹¹+Ñ¾~¸1I]5\ZK-¦9\äY\çh,’»\é$\à¯ýw/—q\é©Ä¤ýÁ™ý#\ç\ôƒ\Ä°2Š!\äª ‘?\á\ÈUµ\ÝI\ßa×¡¶\ê\ô\ô„¡\Ão³‡R¢ý··s|;½lJ\ÒIk\Ê\Ë,\ÄI+™\äCuyµ±¹^\Æß»j³T\Ë5L•\r­\æ”2¼„s›¨6-\î-ýŒ!\ÊG&§6¢g†…c&gL z†¨Uæž \Ç+F\È\Ä3\Ü\ím­±¹\áÏ²l\Û2ZJ\è£Y\Ü\Å)‚\"\Ê\ð-Ä¤\\6û\í¾\ß9VWœ“/\ÄC)Ò—\ö¾—¿\÷¿\\Ys™B\éÚ‰`_N\öÀ\õÀ\ñ\óV»jV\És*\ì».J	\ò\ê7š–ª^Ö¤²4Q“­£)U$167=~ýfÕ©_C-56X°\Ô\Ê\ÐH\Ò\Æ\å ŠX\å\âjI\Þý-mµ\Ïyu*©\×k¨-\Íqn\÷\'\ê/Ž9AÔ – \Ìvµ\ô\÷Áü\ËH6[\èC\ÙFiO\n\n˜Ls,g#º°•¤[2Eµ•{\ì>·¹â¬¬WP\å\ïL\ê\"\Éx”3Æ‹r\Ù.WH\Óe\ÛWN¾ƒ\\\Ôr™!tc%6\Ì#|G%]k»HjŽ¹Y´~‚\è´Û¶\×ÀüÇ“*¹†Í¦‹&†Dx‰B\Ç+–V‹¾¥k”·[\ò\à\÷‰`¦Í¿AA	mQG5;\Ék•BRÀ\Ø\ö\ß\ï\ö˜Ý™v¸<\Ý\Ì-`7\õ\Æ(J\ñ1­\Ìn;|\'¬\ÆLf*ù—üM%x\É#§¶ŠH\ä‰-\Ö5DUw#Ó®øE‘\å\É$mR±\ÈE\ö`\í;\óF¸\ö\'·~†[ÝVš\án¼ºTX\rû\í‰@¨\nª%\0r\0øosü0­™‰…³}\ñ®<\ö\nzE§¦¡Se²\ñIU,N\Ì\Ú:[~]•+h\ç\Ì*¯0©j¹m¡,ŠB­Í¡[ù}q\ÔSJ	&7\n¦Àµ\Ã~ãŽ´³+\îU”¯Pqú\ç\Óú\á=Gùœù\Ù\Å<AM«CŽ,A‘˜.‘\Ð0Ï—x†®Š\è¡}s;»\È\ÍfY¬  5€c!*§.\Æû­\ÏlfŠ†V+	\î\n\Ü\rýN\äe\æâŒŒ&§£—7¬ª\Ì%¤y%¨«²i†d‰cTP\í\ð€¯}\ðw\'\ÈW+\ã\Ô\É\Ã8DÐ¤•P¦\åu\÷ý\ß\\1\Ô+9fnl\Z\Â\×\Õý\í‹t\í;¤§5gLŒ	a\ê	þq—\ËJ\âÊ¨lŽel\ö0+b&\rM$D\Õk¢™\\ \\_¾\njG\rª•FÀŽ¡	À¨\éƒË—Á-šIÙ•V=Ok]º\êc\ô8™2Ü½ZÒ³–Cª\ö\Þ\Í\ê	\í\ÛlM³\â1gbÀ\Ôr\õŸG,6 \ò©a§H\'°Ä±\ågKt\ónt\êcce$\rþXa™ld\ð¸œ\Ì\06›ßµ\ñ#@ˆ\ìˆ@\Ó\Ê\Ýj¥\ÆþØƒg3¶·“¿EF„/¤…Óµ\ÍÁ½\ö\Ç¢¨ý¾\Ã&Z\äA&\Ä²ƒp6½ºb½\óY?\ñ®\Ôo˜µt+Ø›þ$Š^\Ì\Û\ï¢\Ýz[l\\JzxLm`5´‡;½µ\ñ\ñ˜\Ìo’by—\ô*Óµ\Â1ú\â6g¼£Q²\Ók\r´\ô\Æc1Ç©gÜ­\É\ßr}Í\\I&\ÈØ·\öÿ\0„XZø\Ìf\â#aø2±$²µ>\÷\ÆiÐ‘ª’„OLf3:t)\à20\Óþ\îbR4D\Îþø\Ø6Ga Ê‚Öµ\ô¶ø\Ìf\ÇY=•J¨Ò¥\ì¿Cq|Ty³\ô¬ŽH\0Eú\ÛŒ\Ç|N©Vý\\ƒ\ë\Øcp~\"!k]\Ð \0I$\Ü\íŒ\Æ`x€N@‰\\lomºs9€ Fo¤\è\Ä\÷@\Ø\Ìfw•€Aº=‰q}K\Òø²°FË¬\ßZpA\Þ\âÛœf3\0u\0²¤o>•_Ãˆ¸ï¬‹œJÈ±»s¥•>‡~\Ø\Ìf4\ä(q#1$@{]C·)›M…”v\÷¶3‡‚b1/*\ö@¬¶\õ\é‹\n\Ír/m“q×ŸcŒ\Æa \êXŠ\ÈiM\ËkX\ÚÜ X\ôþï‹”\Ô4²A$Ž¤\é•P­ùXp‰\Ü}q˜\ÌD˜\ë.I–QGK,È®$Ñª\å‰Ôƒ¡Û¾!ŽŽ˜GQ-›^±¨“}[Z\í}\ñ˜\ÌL\ÊSEB¡apL\Ò\'°\n\í\ö\ß\Z¬³1\êQÿ\0S„\'Œ\Ä\â\È –N\'W\ÄT‘kb\Õ\Û\ö\ßŒ\Åc§ÿ\Ù','2025-04-15 10:25:35',1,1),(3,'Family 2',_binary 'ÿ\Øÿ\à\0JFIF\0\0\0\0\0\0ÿ\Û\0C\0															\r\r%\Z%))%756\Z*2>-)0;!ÿ\Û\0C	,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,ÿÀ\0\0´\0\Ö\"\0ÿ\Ä\0\0\0\0\0\0\0\0\0\0\0\0\0\0ÿ\Ä\0B\0\0\0\0\0!1A\"Qaq2‘¡#B±ÁRbr\Ñ\ð3‚’¢CST\á\ñ$U…\Óÿ\Ä\0\0\0\0\0\0\0\0\0\0\0\0\0ÿ\Ä\0-\0\0\0\0\0\0\0!\"1A2Q#aq‘±\ÑC¡Áÿ\Ú\0\0\0?\0ù\Ò\\`ÁsŽ™Ú½k‡‘Ì‡™\0m\ÈEM*R‰1’«ŽT\\“\ëA\Ëú\Ô\ô\÷«\ã\íq„Lø\â¸Œ\Zº5|°\ÈÀ\çP[RF\ãzl¤i\r²\È\ÚF·+Œø`\Z<qË€ø•b\×ÿ\0$c\÷›z\Ïp\Ç$\ç­6±m/`yoŠDp¸\Ø\ÝFV–1ÍŽ\ÕÙœ\ô\Íj¸²h\ÔH­\Ý##>\÷¯ZS\Âd¶ŠC,‹\Þ>âž‚µ\Ü\Ûgs\Ó\Ï\ãYY²95ÇŒU˜A··…X\Å®q«N\Çm³š²\ß[}ƒx\ó\ó¡e¸rBh$6\Åjs\×|\n^\Ín\Z€:œ±f8@[|\n\èG§\ß\æh¡Œh\ä\×}‰oxzW:•.¸½\ÛA8Z\ZG˜\î›b»·\Âds»\"²‘œÑ”|Ë†¿P	\ÞV=\ö\'\ãÊ†g~ZŽ(™‡y³B5ic¢­w	·WŸP\ð\ó®‚:1\Åk1†Pz1š\ö”`Í½/‘^€»†EV[3?Ä¬Rmrª4s\ã(\Ã#,9f–A\ÄoAk\"\"È¹‰#—€­\Ý\àœ\0\ç½$š\'f2Ç¿\áe\È#ÐŠcb<„[\"€|LŒ\Ø\ß\Ïl‘G\â®‘Y\ëy\Z9T mYo:\Ñ\É}qi®	Ž¨1„N<\Z³²ž\Ês\"\ëF|(\Þ\Å«€\r‰¥IÇ³ƒ!\Ã(m]0+\Ø.\áp\ÇZ°\\užK‡…e\æ§\Ï\áB†”`À\rú\ãu*¦§£5n°\ÎX\ç˜\Ûz\È_C\Ø\\Êƒ\Ý\ÔY}\r5¶Ø\Þ\Ò\0;ƒ\Èú\ÐUqr8eu\ÔU\ß5Ø¬dœ\ä2\Ü\ò9Q1‚1\ñ©^F#Ð¤““R‚\È,È¹sÂ±’¬\Ùb6½*–0?Ze¢	™µÚ‘¨`l\Ü\éuÂ²±PNC`\ç¥L«™m½¡ƒrQ¹&¬h#\È\í6<\ñÒºZ u±¦6¡£IeuA–v<³Î Y\÷\'B5Ž\ÚÑ¢û¥AŒ’yúÕ±*\è\ÔÍ=ˆ\éBi{b\Ýf,§ži”+iažE³¾M\çp«¿Q§Œ1.\à”\Æ\Ô\Ò+J\Å©\0j\È\É\ò¥©pt©‰N²q¶)…¥\Ã\ÈB\ã‘\õ¥²c«Ž\ã\Ý\â7\ÙI;\íµÚ°)*,š½\ï\nco«\ó\ñ¤J|ˆ\Ìb¶ß•^“>\ÕDJ\\F{8\ÛÖ¥A> ²\Z22.ü\èI ‡N5\n1¡eRAÞ–Ld\É;\ç•X«^\åqQ\ôu\Ü\ÆUšu¦SlÐ¥ù\ê5\r\É+f\n\"} O:¶k7TœŒf¼v`ºzUdWT\Æ\çnC5Gwû„º\"úŠ\Üc\"¨,	\"ž{CÛ«\nWuQ\È\åHÁ\å\åD\Å\ÉWn¢\'”vŠ\ï#„i#z\Î\\Z\Îuw\àžµ¤–E|¢¶ýhsn¡Im\ò7\Í6=Ø‰2\ÜË¡$\Ã6œ\òcLm\çH0\'\0§M¹m@\\D\Ö\÷\r„f8\ô«š™£b6\Ó\ãR\×b ¡r\ßÛ™E‚7E\Æ\Z\Ùä¢\ò­¯mÖªHû=²‘ƒµq}4rÂ®\ê7oÒ¹\ï5$ŸÈºV8\ó\ÏúT®á¤A\ð*P\Ù\Ð\È\0\Î!šx˜•9,\ß}«\ÆfgÌ™\ËøQV±\Û\È\È±Ï®3\åUq’9Dr1–\Îv¦¯¡p¨¬žT2©\0\êFw\ëU2½³¤‘¾\ã9\äj-\Ä\ÑB‘\êÙ€Û¥T\ÌH\í\Ô}vª\ã$‘\Ú\á\ó#v\ßm½(\ç‘B,k¹8súP„°ª‡3\éÖ²h\"P\Òc^I9\Æs\Ð\n-´*hTq´vh½˜\Æ\0=(\ÈX \å†\ÍSmr’û €1Î˜Fˆ\Ýhnj;Œ_¨U™b\ê_©¦Í©\Z\Ù‘“šyQ:\0@¬Ü„vûWs\Û/p\Ì\Ñ\ë\çAGG\Âû´~\0£cRMˆ–b.B\Û\ã¥\rqlŽ-Š\å†\ÔFÁ+u6\")a ¨)ƒ\Ëj\ÐI\Zœ\íK\æ€oK\÷¯sAhž@0p7 [V|)´±’()žEY_\ñ\nVTod\Îû`\ZY3—uÞŠ•3ž•A}¨ø\ÕA° 2:1cw8Á\×] ˆ\ó\Î*Û¢\Ùe\n|Ž)}Ý´†1‚A?*c¹¢Œ±=\ô\æfS€4¼\è»y\õÂ®‚§½\ËÁ°\×	}D6ùP03h‰Ù‰\È\ò¦ƒ\nS`Ù‡Ý”‘#u!\ìH\åJ.Õ€1Šq!U´xA\Ô·\\ƒœR‰w`v®\Æ<µ*\ò\ëiŸ=ªUBA¡T\ê:Ô¡¾\Æ\ê@j/’)#\Õ)S¬|q\\i\ÆÀ¹\äu­cY\Ú\\\äp	\àAü\ñJx•¥¼r\ÛŽ;y48\Î@9\07Ö‹ ¤V\Ì\ò4CH!5\0q\ó§?\Ý\ö\ÑE¡]Ê•b\ÌN20rqµ3†Y\Ç@„€¸\ËÓ¯…U\í0˜ž¹\02~\é^Y\Ít°J\÷\\\ñ™UQ4\è\ÎI\ß\'Ê¨e–@Kr9ª.dŽ9e@UbR\0\Çx“À\Å{\íœd\åelþ.\è\ÇÀÖ¸!\÷:\ì\îk-t¤k\Ô\íšgœ©=ŠHH\Ël\î\Ü\óL\âÀ\Æ(n¿¤jš^\Z\Ñ\Ë\Ý<úS¸\à\0‚\rel¤h\Ù\óš\ÑCz„\æ²\ßW¸\Ë;2\ê0D\Ï^•v*”‘[\Z½w§pU\ÉcÜ˜\Ã-[Š\äŠ3(¨0\ÐfŒ\Z¥\áR(¦ª\íY™”\Ö7hºkpsK¦³cœxxS—#z \çÊ³#)\Ô\Ó\Æ\æ·=”§=\Ú[iTœ­i$\ëŠ[zÎŠ¬^tL|§º…*¤Yˆ¥ˆ`’9xÐ #\êF_Ÿ\")¬\è\Ï•ž(¡,¥\ÂF	À\'3\áJ¢\âY{¯a/£P1‚|}¾¿jc\È\Ì.¢®«qO‚XŠePž`\òÏfœ<2\ä\ó#=km\Æ‹w‹\æe\Ç{˜¥W\Ü]¨–5=´i«}[dŠ{Š\ß\ÌC.3zø‹B›˜^Uhü©|©§Nz\î)—g{†´\Ø$‘\È\äžjW\0Wœ^‘C˜\Æ\äuR2*\Ë\àý`±\Ú*V#mªW¡jS h\Í]\È{]û\'*pyü©M\ô\æY•‰D£#\ì\ç8¨\×r2~\òŽG|\ÕsMªP\Æ}G$¤üÂ»4Pµ\åÍš†Œ\ä \Z³€\ßt¡­Ê¥Á\ØÉ¨\àT*\ò\0Ú®µ\â\×*˜*¨ùœtÉ¡¤¼{—’=$\öš“#™µQY‰\"” \\U9\Õ#`‚\n¾c\ë”Á8À«.H\äeÓ2\ßf»·„»¨\ç\ÔÖj]o\Ô\Ñ\ðd¼„Â™dOIp>#?\Z\Ð[€G.\÷Z\Î\ð»o‡Oø’g\Ô&T\nmk~5\çI\0üiŒK#©\Ô\0\Z>ƒ …5„\î=is;Tczkl\Ì\äz\ÐÉ¿ru\ñB\ÃŽeQ½%Y´ŠŠ}¡±\à\ð¤·%žIr ‚2’c›\Û?\Ð\årC\r\Í`™aQ\ä\0WË—ûIÄ««„\â F¢—ld$jMklxý—·6’]Z$W\Ze‰ÀÎ™}ü7Á«\ä9Tl@®%c\âc™&À4—\n3“B\ÉvH;\Òù\î›zE¿¸\ê\"¬d\÷\0ƒ‚(\'¸—\'Iù\õ¥RÝºw\ó\ÇJ\ñW.Í¤c–3\áK?‰\Ôe2 \÷\Í~#Q¨Dªù\ê\ÙÍ¼±$P«36FUjfø\n\Ç\\q9d“N\È5\Ú\Þ\ÜK‹}\ß¼a\å\÷evù\Õc\"Œ¬¤&O\ñ98­\ãÉº\ÚB;+8²t\Ç\ò\ç\Ô\ó\'­)\nC/™«\Ô™ø\àš\ð/}|5/\çƒ^\0E\ê&Ûµ˜ú\Æü½ƒ\Ã9.Ö6\åŒ\"E3\Ôã§®:V†;‹r\Ô1ŒV*ÍŠœt•^¹\ä\ãOO<QQM\"°VcŒ`yRÏŽÉ¨\Â\å nQ|\ïo\Ä\î.-Î‚\'y\"#o\å\Ë\õ¡\'¹ž\æWšf\Ô\îrH\0\0(›\Õ\r\ß4¸\Ó\n5c	U:T‘±\åRŠO\ð\ãÛ§J”©w\Ô\à²{9]\Ós“\áCua¶\Ç2v:cÈª£;cú\Åmn d\Â–\Z‘YQ\Ä*Üx¬ý=s‰Â­™º¬6`\Ð\Ø\\´A\åU·‰€*\×C0=R!™®œyÕ‹j‘XÝ›gv@£²‹’q\ã“ùQ6\\–$±9bI$ŸM@Ts z\ç\ô­¬$\Æ{f²’(JF\n©x\Ç\È×«j\Ã\n$g§Â¯Ôœ\õ¦:\å¿LW «\r±\êC\ð\ëN¾$q\Ô\Ê.FCbYOxaWlxg|\Ó[x¤s“\áK–\æEP„,ˆ\0Pe¢2‡Î™Øˆ¥\Ã\Æ\ç}ˆ;2·\ì·\è\ð<\Ï+‰“‘:šx³.O\ç\ÙÂŠG\\S˜‘\n\äRžD(™;\ïOb.‘‚\ÄdŒ\ÒÀ;1½‰\Û\ÄzWÊ¾\Ø\\Lüoˆ\Ä\ÌJÚ˜\í\ã\ÙUTÄšúŠL\Î\áG<\ó¯\ñ\Ùû~3\Æd,¬Zúa•9 `\ÓXÑ‚v±Qh9þ/¦\ri>\Ë_½¯†XŽ\ô‹Y\å«Ñ·®F?\ÍY`h=#ü¨˜$u•eBCG,s!^`\Æ\Ê\àŠq\ö¤A®‰\õ\Ù5ï½•0%3W\öª\îs¸8 ùê»“B\ó¬\ÐÑ®\Æ,¡R¹\ß‘\\K\Ù\È\àg>D‹¼\ää“µ$¾¥À\'\ÝbG•L6.WYF 0G:*©qy4Â¸ý\ô*GÆ‡\ÇD¦7Ï¥y‚qq4Ážl\öV\ö\ä™úr’4ª\õ’|:ˆec \'+€l™—ž>\ÂG‹“G!S\á•j }\Ó\æ\Ã\ä\Ô\Æ{#r\òL\Ñ\Â;i ¶r|6À \'µ¹·\ÞU ¾\Þ5¢À\òvt¿,‰´\ãø\ñ\ñ\'jl–\ÑÈ¬v\÷—m\ðw¤A—l¶|ž}0sL\Ò\êDX†NB¨lt¡°?l|\Ê\î£h\ÒE>;yŠYŽ^\Þ\öu’<\ç©øR°¹*<\ÅJ[”j½C\áTXÚ¥wÙ¸# \åR«b^[\0%ÙŽÆ’I¿]*H\ãŠ8\È\0;•R>#5\å\Ì)¾¨\ÆZ`Q±‚\Ø\Ç\Ë\åCZ¿i\0\Ï8Ù¡9\ò\Ã/ÐŠk€§a\ó\È\r\ËuŒ\á\ö\Ï#Òºïƒ±\×z\à\ã\\¬¬£Æµ¯\ó£\ÞT$\\«†˜F V<‰\Ç!T´… *[…žw\î\êŠ\ÕTe“r\Ç\Ðý\n‚\ô$…½	~­clþ\ã\êy\ë\à\ï€B\ÒÒ ¢Ï¯|Ò’¸\ÆA_Œg\ÐÖ“¬\Ö\ð¬ƒHŽgw|Žxmþ›ú¶^¼bWw\â¡ú»‡wF”4Ñ¢»(1&­¼|([«v»\n\èÁB\ï•;Œy\Ñ0G4v\ìL­„Vw$\òU‰ùWŠú”\Ü\Õ:\ÔSo\Ë!Ãˆ_²#˜v\ZTBs_\'¼€\Ås}-r\ÇÌŸ\ðÙ”\óÞ¶œW\íG\r\n@c‚\ÔÌ±¨2È—¹$–\Æ\Û\ã\Ícn¤i$¹™±ªIå™±œjv,y\ï[ü\\-m¾`P>ÍŠú\õ8£–\Ùã¶‚`Icj.\\§’ ­%\É\õ¡†Bú<c\æÙ«\áy´ˆ*\Ã\ð²\ä,<)\ÆJ©\0\î}v[X„V“¢\äImnû\ï\Þ1©\Î\Çf\ò\Í[³\ÈÁT\rü\Ï?tº\Ç\íþ\Çc\ÊFÑ¤PÂŽŠ£DQ\'WS‰Kkm\Ä^5\ÅÁAH	û¨\ån\ÎF^™#`|\Íf \õ‡&/~=i\Í\ÊMm#B˜E¼‹¬•$bùSž{P\íue\Äì™–L\àE(\Ãø\ìÃº~uv.I\Î|\óƒ]\Û0†D“$hx\ä\çŸu<\é\ã…@×¸äšš/ev\07¹Í¿„nk\Ö\ì@`Á˜û\ÍÙœ|H¢d¸+”\É˜ÏŠ¶7\Å\n\ä(f9\È\\®\Ü\Ï,xQ¸vT“\äUÔ¤9\Z\ÙÉ‰\ó\ïd#9#\Ó\ç]^\ã:´²\rŠ¸\É>¹$m«(!\ÒRd\Æ\Ô207Ï­0‰¦Weqâ­±À \íÊ´•µF*D	,=œQ\Õ\Ìuø\ï\õ¯L3–=Ô€4¿\ïm\×Æ™¦\ë¹\åÒ¨¸@Ñ‘¸e`Aíƒµ8¾6=„I{HŒ\Ë\ãŠ&h€\êâŽ»$.ƒ\ãú\Ð\ö«ª\â\â\õš\r.\ã§\î\ê[©=ñ¶‘Š•l¢7).\Ä\î\æ‡U\àS\È\Æ{Î)\ñË¹\Ç\Ð\n\ZÔ7C\÷£“\èTÕ·EY\ÙWe¨Á	\Ý\äw«c…\"·R\ÛIpXÄ£¬q\à;,ý«CŒB2ˆ¦U¼FPÄ¶@8lm\ç\éU\ê\' Œ0\æ<|\Åz\ë\Ì\ç~„s\ÍV\\Ÿf\ëùŠ\Ýþs\Ï$|o·—\ò£8P\Þ\åÿ\0—\'ý¨¢—HN7\ß<ú©ø\ÓZ)\È\Ë\Ë3\äO\éH\ò‰Q\Þ·†J“²\ÆLÀ<Kl7­7ŽX,ø}¤\ò\Ç-\Ìp¬R2ŸrH-\õ>YÛ¢H„\÷6‘µ\ÜÁû{\Î›‹Y\"¸¸uH…µ\ÎY\Ü(\Æ\Êû\îOJ\óü·&±ß¹¥®\×P¦\ã\\Ýš-\ÔÃ“5¨Px\é2\îGÀS9¯,%\à|Z\æ\ÎVtKIQ„‹¢T2b<:\î:\í‚—Î¥¶º2Œ\ó9\ß`)ÿ\0\0G¸[†6¶{\îqAT‘\ÚF«6y\0p>>u98É‰C¨•Rˆ½‰–¿u’X´\òX|‹;~TY2KcÔ¨\"®|«É‘†\ê0qÊª`\ãqÏ»U\Îÿ\0Z\Õ\ë\à**ZœÜ§»‰9d°\õ\"­\0€\Ç\é51$\ã5\çf[:F:°z\n¹@²s\âNsVU$\îU›ZŽ‹@bTŒ\å^ \ÈGU<·­¶\Þ\ÛÃµ¬A\Þ\æÁAV‚\ï\Ü\0Fù\Üo\Ï†µ~Í§f\'La€\ð\Þ8oZú‹]p¾ÁøL\ÜFX\ÖG\á\ö\Ï\r°\'\Ú.JÄ¤¬j\Ã;8?\n\Í\ä«ŒY+¯PZ|–\ê×ˆØˆZ{{ˆ\ãœ3À\ò\Ä\è$\0\é:u¸\ëÿ\0z\ï‡B\ÜB\ò\ÎÑ¤H¢–P$y&TwŠ©?‰¹/™«o¯x‡¹–\æ\îwv‘Ù•ZGx\áV9	m€€9Pº0wcÓ®7øV\ö>²yš33\'1CxM½Í¸‡³W\nª U\î\â…t\Ö\Ð\Â+«µ\Ò1\ä\à¶\ÛW¼>K‰,lå»\È\ò@YY\É-¡™Šj$\äœc&¹¸)w.H\ï\ì\ã\Ç6’c#>¹øPpb8P#l\È\È\á\Ú\ÄP¨.™\ÚP¤¬\ÒFt(‡R\0E°¹\×*\ÌŸp[\"</1\åVG½ŸfpS8\Ò\ë\Ïby]F’I•\Ô\È\êX•‚c¥\Z¿·F\Ôv\'qüy\Õ€\÷S\Ý%µdd\â*ˆâ»‰fKN¤cŒš¾I\åVueU…t«\ã \î[\ëùRÜ—¬f\á0¨\î\"kø\ô\É9\'W\ÃT5\õ¨ cVù\ô®¸ƒ\êxâ«±,·12\óÇ­fï¡Ž\ë\ê\nš«\Øuº• \r\ñR©i\Ë\È9À\ñ©YÀ*j±Bn\'žap\ñÍ¤)™\"•€™@<þ´\Îhš{;°KI\ç32tImÀD>L\Ê>T¢bV’ \Â\èx\Íd\í\è\ËF\Ø\\E%ŽM\Ý‡K9À\"YÀ\ß ü­\\B\ÙH™\Î×ˆÿ\0(¯¤\ÊH\ñ]›\äj‚\Ð6BÉ¿ƒ\÷HùÔ“^N’\Ç\Èa‡\È\æ¨a7Xÿ\0”\È× ½Lj‘\ã\æq\ñ\ã\éL¬T{,-¹)$\Ã9ý\òiV$\èš‡_\ó¦œ7Q†dn’\ê…E#\Ê¦£¼RC\Æ\ÑÉ¡ uaqnTl\ÂUÀ\ñ­\Í\Ç\0·µK«‹ž\"î±™5«Á	Š\\œK“¨\ä\rýqµ`#™-å´˜®¥‚\æ	™9j\ÈøxUPq•–\ò,\Ì\éu8rK<\áZ=Mž¸8¤“Š¹\ë°\Ô/\'1\Ä,\ÒeWv u@8F\Ã\Ï\ëDp\Î&\Ü\Z\âKž\ÈÎ†-¥\Â:…m`¤…O\Äc…	mš¢ù\ÂC+¶žy\æ:\Z\ô™0cË‹é¸±<Š\çÈ™{©\Ü-\Í\Òqe´\â}´ÊŽ´2£\éšl«m\æ=iVŒmUv\÷1´\í˜Cng™t€t´ºucn¤†½™ž¶:´¬\ñjh•¦…†Ÿ* ®\ß« \Æ:üOJ¹>¡\0ûÔ¸\Óc]h-Ž\é8=\à3Œ‘Ê„;K¥Yd£&Œ€kÐ¡œ0|ˆ®\\…ã•„w«Ú¬°Ü–\ïc¼\Z1‚£À„g‘©5	\Ð\õ}©\ÄdÉ®0Àv®SWL1Æ£ù\Ó.5\ÄÛŠ\ñK»¯v\Î;{D\ß	mnq\rú‘\Þ>lh	\"fši\0Áe¼h¤Í˜\r€ø\Ð\ÓbIÁm#`:\Ó\\a\Õ>\à¹\Ø‘¸ ‚ \ô\éŠå•¡Š<\ê–E}\\…\ÏÖ«\ßVžzB(þ\Zª\÷\ö²¾”Š\ÙZG2\0w‘üNÿ\0\n\Òl¾ 1ù	¨²†\â]£…0§þZ3Kx¬3YÀm\â3h’[™‚6±\õ\'\áDL\å»8\Ó\ßn\à\r¶3\Ï>T=†[\Û\Ø\ã\Ú(YbˆzP`¾‰\ÉùVSd8ü£©Œd=dŠ	£‚Ü´¬ù@{Nc^2W•tdp~\ò\"\ì§sc Š&]\ï†;¦)\Z„.½ \ÒW\ò\ô>\Ë\Å8%\Â\Ôf94\äA Õ\î;*Ã¡£$7pJÝ›¹‰Ž0Xü²yP—\æH.#\\¹GRï‘»¶À\Õù$ ‚9b=AÞµ?d\í\ìn½!º³Žc‚hY\ábQ_Z\ã>5›úžC‹Œ\ÏWUþe±\ÏCS\çWg\ï3å¸¯xs\â\ê3zÖŸ}–\ã²\ß_\\\Û\ðý¬\äÂ«\"Y»X\î,x„\ru¨ŽB\\cm\Åd\â\äcÍ€…>U\ê\÷ ©\ÜÐ‚¡Ž¡³¸©Nb\ãœ\ìm˜($€¦¥y³›’?\â?\Þ\æb\Ñckk¸y\È4\\\Â|\ãd_Šœÿ\0“Î†´Ô¯r@\Ø\öj}¡µ;\á<\ñ;y¯,¬\ã\öHuf\î\îd¶·8Î­\'07\É})\Ò\Åkwqh’DB\ÒC–´¶5£cq\çŠ\öœR\èlˆ²ùc+<‚\ÄC>¬?Z\ãC\õ˜½\Î~U\ë<Oº±\ÆHØžžµY\Ü\í+/ÁO\çZƒ:üÀýø\ó\ÎGoVlQ\\=\Ärº\Ñzg\Þ]ùŸ§–©¤ t\Â\0|Y	H\Ýj%X±$ù\Ð\òdWR*\Z2°1µÁ\"3\æ\Ã\ó¡m\ôûSŽX	\ó9&Š˜3\Å\Ü\ïgc\æ(i\Ö\ß[MÁ\ÞF\Èu\Ò6\Øf\Ø\ò¨\ã5P29ªY|EÇ±É¶þv\é^\Ý\Æ\Ý\Ã+6T®•\'#—\óþ….N „\å\ÞD\Æ\ØVÀ\ßÝª\î®\äx\Ù“\È`\É\Ú9\Îù5§ûJ\0@\ÜÁ^BÀARFvP¯nº”\ö£?}¦I2‘¾<w\å]\Ëi¤[·g\Ú \æU\É³Œ/1\á\ç\å\Ô[SŽ\ä\÷K¬q\Í\Øa‘§*º\â\éŒ(\ÊK€\åH`\ë¤ÌŒ}<+²€/¯™\é1œx\ñ±o¸úœ[Á’H#’C\'c\È0 F\ä+\ó\áúdQ©¶¶–Šª\Z	4\÷µ®F–¹u\Ï\äE\áúa22K£’g¸d„\0r4–\ê><\×w\0²$\×&=1q*;0g wŸPn›|±U\Í\Ü\ä¡\êQN>\Çî¿ŸU;·Žb\òO¤K\"v\×g«6=\Üø@:\Î;v’<\ÚU{BIP0t\õ«¸¥\ñº.€°Š9oùž~ž0b0F\à\ó\åZW\n\ö\Ñ|\Øûc\ðøžÉ‚¾\é\÷‡0\äE\Ã\Â\Í\í‘2³¼…YŽ@@¥FI<\óš¾0d±\Â?\ä6þU©†\Æ\Æ\Ò\Ã\Ú-fY&KC%\âÏ˜™\æQ,5rS\0\ò\ç¾<œž4§qL(.\Û\Ô\ÎTŠi`¸uÍ´ Fry\è\í¶z«/“\ÛÊ³!+*±,FI;«\n¸\\biZP\Òu4\Øm$¹\ÜpyzWH\Ó;Hø\Ô\Øt\0†¢\Å4(Ñ±5\É9\â¼.U\Ù.UD\ðm.³Å¾œ\÷\Ü|k/\í\ÜGŸ¶\Ý\ï\Ï3\Ë\õ\Å[\Â\ïe²¹M»#Žxa\ÔUœF\Õ{Y®­1%¬\ÌÒ‘\ï@\ÌrQÓž3\Èÿ\0*·®2Qÿ\0¤œª\\\Äx \Û\Ûn~21ü\ësýœqn\"x\ÍÅ„’™ ½´yd\í\ä:£kn\ò´Y\æN¢\ð\ß\ð\ï\ó\Êú/\ö[h\r\÷\âRX\áµ[‹²R\Ê\ë3\é\Éü!W?\ÅUýQ\ñ@G\Ä5n\àÏ«2–\È\ñ¤œG€\Ù^ƒª.7Æ\ö\Ðo\÷±m\Ï\ïo­x²E &9#p	Õ€>©5\ò·\Äo°±5Aø3/\Øk\Ð\å\í/{=[a‘Z•\ôÎ¥H\årF»ÿ\0‰W\ñ>9\Æ>\Ð6\ñ\Â\ÜQ\é½¨Íµ…º“¥K\r\0az““ùV~\æ!y\Ó\Ú\íeh¤h\Ë[J²E&5#eOCŠE\Û[\æG\ò®ýš\ß\ñL\Ãøtšú\È\Ü[¸\õ))û¯Â¹\ì\Ó\ÇýÆ‰\öks¨,\íœ „ÜŽ‡q\ç\\Cj\óÄˆ¡\"žW\Õ\Ï\î—V•ß™¢ªE\ÉÜ«³´\Õ^\ènA\ÏÎˆ‘\äiQ\Ë$¨\ðþ*kk\ö~\Ê\äx\ä“Œµ|ûqA\Ë\ÌÃˆ[Ÿú?\êH\Ü\Î\í ²‘\Ævea¾4Z\ß[±(`—I\r\"«&x)\'_\àV¼>4<b\Ú\ñµ„h\ã€\Ä\Ê\'VLŒ<¹R\ë‹û…·€¨fVb\Î@UU\ÎHü\ë“>,‰\õ\ëúÿ\0\ì\'\Ô:O\î©Q\ÔD\ö\î§\Z¡]˜\òÊŒ`P)d³e{G+¶nC :\ÒA\ö3ˆ\Í\'fxˆ\È\ÔÚ³þ—£\Ç\öu\Åù¾<AiCTN\è5ÿ\0\õ*\Ëü&%mÊ•*\Ì\n\ò$Š²(\Ñ4Šd\ð\Ã$j\Ô1¹9­‰þ\Îø\ÏN5ÀÏ™’qúVvÿ\0…‹#(‰\ðþ!\ØH±\Ü7\r\í\Ú\çN©¤P„œ–\å¾:™9\ò}¦Vˆ,L!„•mJeB§¦\ä¨ùU³\Ü]\Ì0Jø‡{½V\ð®y\Æo\Ê\Ökh¤1K1’\îFHUc\ÆÄ¨\'\';m]\ñŽ\r{Á.£´¹¸´šG·K€\ör¼‘…vt\0–Ps¶ûTŒ¨¯r\Çc©Šž}D¶\ä\ä\à\áƒÒ­¶‚\Ý[7-3(Î˜\â\ny3ÊŽ\á¼*~\'\'d·ü6\ÑÙ´@8•Ä°–\0°º\ÆÉ‘‘X\öž°?i¿\ë¸\Ûý\á/œ5ÏŸ4\Çr\Ã\ÔH“\ðøGkŠ	w¨N 9}ä­·\ÉO­	ys}vP8D‰R4\'H>\';“Z_ý\öŸþ¯Ÿþ\Åÿ\0ü«7\Ål\îø=\ôü>\é\ây\áX™š\ÖC,$H‚A¥\È‘\ßj¾<\È\ç\ÄÙ\ÇU=»³,\òW>\Îÿ\0´>F­\íüÞ¼3\ßùQ\ì\Ê\nžE¤‘9 …`\Ä‚q\çDE%\Ìr30%‘\Ý\'–Fÿ\0\ZL:kù\Z&\Î›ùýž¡\Ä7e}±\Û\Ä\Ó9\'\ÇÌQVü™`j`]M¤¶œœj\Æq\æE?\á¼Gˆ\Ã[C$‰K¥V<\ç4½¸_Y8<dDO‚;‹\öl®ºˆ‘‰\î²\ò`G?\æ›A\ö?\í4°Cp—.4š(\åU~$« Y0Nþ;\Ò\ÙùUxÂ¿Œ\r\êý\ã\Æ1ž\Þûqƒ‡…@\ñ\÷\ôª\ã\âüf\Î\æ;˜.oU+¼’’’ø$BJ•\ò?Jþ\Ì}£G1µ\ï\Ô6Ûˆ±Eª%\àn)6¾²fr0RúFSŸ=4—\í<F\ñ\î¹\r>¡\Âþ\×Z\ßGÿ\0¸…m®Gh­ \ìd\ñhœ\ô\ò;>u+\ç\Ñ}•\âˆ5K\Äøf[\ð‹†r=N@©^/‚\ÎJ\ä¯\é\n\êd¹Ô©R½Œ•\íJ•3§µ6\ð*THù“\å^\ìy\ò©R¸É“Jø©¥?e~B¥J\é2iO\Ù_¯p*T®3§˜b`W œ\0=\0*T‰`o\õ\ó¯4\'\ì¯úEJ•\Ò$ÒŸ²¿!S\0r\ØyT©]$IS\êT¨“<5\ÚÝº*\Ç\ØÚ°]µIn\ç\'VK0\ÎÜ…J•\ÂU¥‚þ\\\ØZ`’ú{\Òu\÷G!\ð\Ç\Ón\ÖþA\ÚK\ØZvŠT«vI`H©R¸{”’+²\æ`m¬\ô¥¼³*û:iÖ€(8>»ø\õª…\ó©È·³¸·@IŽFù_ø•*\ß2G¸E¤\è\É)kK\ÐbE\Ý1¸l“\×;µ*T®‘?ÿ\Ù','2025-04-15 10:25:51',1,1),(4,'Family Beach',_binary 'ÿ\Øÿ\à\0JFIF\0\0\0\0\0\0ÿ\Û\0C\0															\r\r%\Z%))%756\Z*2>-)0;!ÿ\Û\0C	,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,ÿÀ\0\0\Â\"\0ÿ\Ä\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0ÿ\Ä\0B\0\0\0\0!1A\"Qaq‘¡#2B±ÁR\Ñ\á3Sbr’\ð$\Ò\ñCT¢t‚£²\Âÿ\Ä\0\0\0\0\0\0\0\0\0\0\0\0\0\0ÿ\Ä\0!\0\0\0\0\0\0\0\0\0!1AQ2aÿ\Ú\0\0\0?\0\ÚjC æ£ˆNr\ï\Û#\è*Yƒ\È\èA5\ËÞ´Qš‚<#¨lû‡\ïFI#\Ýpq«o¨§\Øº»fˆ¢NÀ\Ó\Å\"gq…=ú\ïG\æF:ŽÔ†\rQ\ÉsFzdƒQ\öˆ\Æ\ÇýjK6¢‘ý\é@&€\êw\Û4û\æ–#f\ï¿\ó¨2zŸ\\Ð€|:Y\'¦~µ¨\êsO­<ê ¨ˆs\Ü\ÔJ7sR2yt¥¬fœ\nD#žŸZF)O\æ4@\ëSG`\Ù\äó©­»\÷cF\æzÔ„‚ŽÃ \\‚;Ô„dQy‚œ05. AH\ïO‡\ì(\ÛS`yšC‰<\ñ\ï5 ø¿Z 	\×&˜ÿ\0”Ÿù\î¤ªž§?¥H\ôÝ°®\õ«¾i|¨\Ì\ñ¨\Û$\Ð\Ú\\”\çÌš&œ\Ó\Ïz\0®d~\äþ\Ô\Ü\Ö\õùÑŒc¾*\r\íŠ¾{~´\à±\è0;b¦\"5-!EK=\éT²)TÀ1Å¨9\Ø\ç\È\Ò€Ž›úž•p\áŽOBŽÂµ\äÉ…¶\Ç\âÁ\Î\ëýhžÉ\ÔQ\ÜU\åŽŠ>5=jÂŠ|˜DQ[&\ì\Ø=\è\éfFC@\ö\Î(ü\Â?(¤^V\ì1ÖŠ\Â \ÅI\Û>™\é\ó¢Eh\è|?]\ñDc\è*X—»‡Ar3§n\àPe·œþÒ§¦O\ã=1Ö˜£\í\ã?:h_Ùœ]±\ÆGC“\î8«eø‰¦\Òj\é-•Q~{¥+\Õ1y})rT(Â¥ƒV\ô\ã\òý)µuøQ\È!TS\0\Ñ\Äµ?$\ÑP@85 \r’w©›Ê¥² 0®jB74P„Q5#!JŸ$ùÑ‡®*[Q’<\éùK\æh\ßQ$¤|(€”¾µ¥L¹\íP,\ÞT˜Áº=(aG‰#Ê†G­ °:PYJ!¨cz	\â\ò¥F\Ò=)P=ühŠ´¿\É\ÈúŠž™O_¦\ô\àPÁ}j@/sBXÛ»QB\ÜP\"@/˜©`R¾u0Á@ÈŠ•K1ú|\ê%£ÿ\0/\Ìþ\ÔÐ†§Ç¡ù\Z+\Øü³O«upT–‘úÔ€QùOÖ ¼\ÍH4‰¢\Ê\ß*—3\ÐÁ”o“S\r\'þj`\é!)\Û\Âiù‡¸ú\ZŽ¶\î¿*w\rHc‰—ÿ\0\Z}CÉ¿\ÛM\ÌO#\ô¥\Í”\Ð¿#N7éš0ÿ\0\ëO\ÌnÀR=5 ´0\Í\Ü\ï\è)\ÆOsL	\ì:š|¯­D-H\n \Ô4\Ñp)(€MDŠ1 EKCA\ô¡°£Ö GZ@\0ŠŽ(\ÄTH\ô¤ TªX\ô¥@Š8\\ü}j`/¯Ö›;‡_: >ƒ\çQF ‘¢þZ`O§Î¦ºª„\ä*@zR\n—^\ã\çN„_O¥O@\ò!HT¨H\ò_•-+\é\ò©mKj`D\Çþ)\ò—\éO\ð§\ñyPj”\Ò\ÕþZ|\ZX\õ¦j!\ò¦:\Ï_Ò¥ŠX D4Ô€©N#Šp7©`S\â\Æ¦0!M\ôÔª\É•5,\Ð\Z©MRÆˆ\Z‰¦j5 E@Ž”B*$R CªX¥Jˆ\ÎÀ\Ï\ÇÒ¤\0ª>\ß\õ?\Ãü\êkH\Ò\Ù\õd¬“c/€*cCûB\Üut²\Ñþ‚A\È\Èaª»‹\Ã!\\\çûIo\Â\í‹,	4Ì¬PHø‰G›…>ƒ\"¹\î\ö\Î\ò\ãˆ\Û\Û\Ü‰š•2\Û\r\Ù!º~aš—¹\Ùk\ô`})\ÅR\ö\è`W\ð’\ç¨8\ò©{t[øzzŸ\åZR•/Q\öøÿ\0€\í\ïþT\ß\ÚQŒ}\Û|súb˜º/\Ó\Ö\ö’\î9/ŸP~˜¤x˜SgrzmœŽ)‡F…>*‡\öƒ\ç\Î\Û\ô9>\í\óOý \ßûw?1ú\ÑE\Ñ•R\Íþ}jb\ñŽ>\ë\è\Ô\è\Ëu‰\Å>\ÐCÃ®V\Ò(\ÄÊ¡§-!Dˆ¶\á6“Ï–~W\Úýu8DN\í#iQ¾7&¸^%q\×\âQ\ä\Ç,Ä¦z &¯Ž3X|\ß/Ñ¯ÅŽL\íøwµ\âBA\Z¼rÆª\ÒFø8\r¶U†\Äf´kœ\à\í­¦‘‘Á™\Ô\0\èË„QW=A\Ím-\Ñ$ø:w\ÍWÅ¶òž…¬¤\â,Ô…V\öƒü!3\Ë5¢\Ú\'‰bŸjŸ\Ëú\ÑÚ´ZL– ø¥LN*B;~´6$H\ÓT§øG\Ìÿ\0*<ÿ\0\0ùÿ\0J‡¤T	Q¡œgÀ?\Ýý*&\èƒý\Øÿ\0~?j–\Ð¨‘@7Ã§)s\å\Í\ö\ÔM\ðú û¥\ö\Ô\ÔŒRª¾\Þ?Á\ï?\öÒ¥@\çP¶<\0\çb2=Nhª±t\Òz`\ämŠÀUd\'JÎ»†:4€Fqÿ\0ª3Vøa•s\ØÉŒ2\ôß–Æ‘\âD l‡I\ô\Û\éR^V\ä \÷\öøœV\\rG(Rš	#mYþ\Ç\ö«Q‹¡¡F\0\ð€¨±0\'\Ü0h£8?´\÷²\Þq;›QÉŽ	MYÊž^@*\ä“\Ð|z…\öw„\Ï}w…Å¬n\Ëqs˜\ÈGTÎ•V9,2À8$A\ökÇž\âY„«o{;O#Ñ©epWÉ·\Z\ï¸]\í´\örM²B9Ñ¢)\n9f\èr\ãy[eTœyW?\É\ò<E:;1•/Ù²¦\"?+l2I>¤b¥®1œü#?¥g\Åqp\Ê‘\"’­¢F`Nü\"®§Sl\ìN\Í	?)5\ÑN2\ç>\r‰:\ç[¢ú~cSIbr4bpHR+;š\ì\0\öu\ZŽ°ÀˆÁZ’0ˆ—ÿ\0§!Ks˜$js‘–l\ntùþ\Ùp8®\Ú\Ú8§™#%$¸Œ(B\àDh\ê\ç‘\î­\Ëk\È/!Ž\æ\ÛQ†A”3G,,\Ã\0ƒ¢@+\Ëø„\'Š_‹\'\Ã\ÏiµÇ¼q\ójÿ\0…IÀj\ôKl\Û\Û[[\Ë\ZH`‰ SÁ\à\Zp¡Žq\åR¶Û†\Û\ÂYL¾n£_Ä¹\È?Ý‚w!L\×q\r\'R\àü.tü@\ÅVK›5$˜\Þ1\Ó%X \õ\ñ>•`O¤Tc?\0*¹À±\É‹¬4dnFœ“¶\ÝNù¢,ˆOL¹\Æ\Þ^úÃ¹\ârC\Å,l¹15´\È$’lA›V\nÃ€F½k$1³‚\Â\Ûa\É\Çr¢)-«\nxiSœûC|\Ó^{\"#´\Ê	Á”\î\ÌGŸo…ƒp\Ë+¨¦¸»Œ¶0±\Æ\Ì\è™F\Îûu¬‚\ÑOsus&>\ò\æYB\çb¥‰z\èl.\îî¦…Xc†C)\ê+*3\Ðo•y\Úùù–\Ü&\r\Ò\áTcH\0m¾\0\\y\r\ê<ßŽ78\Û?:£($\Ç \ëù‰\"i^¡\ât\Û0«·¿&»©\Éû@B\Ð\Ø\÷dmEI\ã8\Ø9\Æß©\Í•˜\Ó<žm­§\åEHÊ²?\ÄmX\ô\ñT]Rº,¬ŠHº\ô\È#\ö£U•%\ÎC<Ž~›U•\Îzúg\÷®Ÿ¶F‡\Í\õo¿Ÿo\éG>ú¯,¨€œŸÜŸJ¯‘ÿ\0¢\È/¼9\Ãtþ$?Z3€rb\÷`ýA©µ\Æ6f\Æwuzu¡\Äÿ\0\r\ó$0úW5_¥\Â,\ÌA^h\ßü¤\ããŠ®C~iSÐcŠ¸$\Öû³þ G\ëLK\á||h¡\nZd\ß°\ì~\ïoÞ†\ñ\ÈÄ°001^\ÝA«„EÔ¡“ˆ\Üo\ëQ/§e`‘‹¯\È\ÑEýÀ~1\ÉJ®\åü ÿ\0iþT¨¨\\N¦»„$k\Ã$pX…B=r\Äü\ÍBK»\Í,V\Ö\Ò5#\'Q\r<–\Â}*Ù¾C\â\Ý\Ïvˆº°\ô\Ò	\çBy\íg‘\Ïy\ã!\ã—(øš´Ñ›D­®f1k’x\Â\çD¤y\ì1\î\Åo\ÝC—%cG~lD3ª[V\\\nq\Êe;’\\`\êVF‘»n\Øý),PL]$’\éÁdF\ÔI\ò\Z‡x\Ü\Å\òUºšEW;)\å\\³°À\Ü\ã\á]?¸±·\æ.!;\\¥³›TˆGËžNYwEŽ5e,\ì\Ø6\ô¢\ö’\ÂoxNµ$n!\ÃZuYc–¸š\'@q’ª¬œGø\÷³À\ê\ñV{­Vl\ò\ób´-­\ÛDJvSœ`q\å\ëS¶ž\ZgnsZ\Ò:x8¼—0\Û\Ü$\ÑÂ“¡s™¾\íÁ*É†\Ø\à½\ó«+\Ä\äQ‡6s´$ \å2`Ÿ†k›ž\Ò)\ç†IBÓ„”\0G&b2³*Œ1F‹I\ò=[KW:T{9•°\ó\Û\ô¨\ÉÏµ^\ZGŽ]Y\Û\Â\ðA\r\Í\Í\Í\Ê[[\Ç\"„P\Î2„>\ìx‡_M\ðx§º´¸½\â<F7k`$Š\Ò\Ï):«`øT`\Êzu©\ñX¥‚9žH ¿·b\ë&§9\nœ±\Å_º™M…\é‘!x\å³u‰\È\nX¸\ð\òuÆ©¶‡Ÿ£?\ì\õû{dbS¬¥¼Vé•ŒCN“°\è?®{t|+ŒsV+[\Õh/cEVŽ~Z¬À/\ãF}‰=HýkŸû/\Ã\ÖN}\ô²\å7&!“»€µ¿B1ý+±<>\Å\åM.¤‡\Z†—|ük,§\É\ém¯\å‰ø•‡5m\Ñ$ž\å“ZÁj\Ë.3dˆ€\ó%ûü*ûE6@Na8NgýeÂ<œEŸ…Y‡…\Ú\Ú †\Ü\áPB!UruxNNw©r\ÝHW\\‚A]D• u\ß8­+û2‰xr¼F\Þ\ð\ßIo=\Ó\Ü\ËË‰\å#\ïŠ®J\ì:\î\ï]\à|= H\í\ãUºŽ\"°\Ëºd\ç\Ø\ê\Î0O]ºU+U·‡ˆ\Ëqy*¹’W‘_FVP\äª\ä\Ó\Ëa\ð\Ò\ãh\ñp®(\ð1\Ðr\õ\ÆpQ%e\Ü\èOCYüy­¶i\òj$‘ç¤Ž\ã—9(C„}\õ(üJGQ\ßj\ë8d‘\Û,‹<ÁL\ï\Z\Â\êI‰•FU\ÕÁœNÇ§\×7Ê—6\æ]!cÓ¨(À\Æ­e=\ôvF]…Œ\Óýž\á}c>%R\ó\ë\äju\ñ¼¾_†«Kj~ž…Ë¼Rp\ìÇ®P¦@\õhªfïŽ wÁŒg¸\Ê×Ÿ\ñ\ë×±\á¶\ì+rf*r\ág\ÒF	-¹·\ïZf\ï\ç\ç¼3˜Rm-©† \Ê\ö>ukf\ë$Ž\ÞP£F?Ê¤!\õÀÚ¢–\Ð\âva†\Û\Ü\r\Û6¡|‘Žg62\ÏMZŽ`\Ù\rf\r†\ñd\Úr\Û\Ð\ò™)À\ö\ñF$„ƒ¾\Äø\ä\æ¬iL‚t°\Î{ü\èQFŽ¤&G]k¥\÷\Ó4r\Æ²\Ú]*\Ý:d‘[\åE\á/¶–§QR1œ\r-ü\Ù\Ò<À\Î\åˆùoU\ZxN¤’\ÙÄƒ\Ò>¹€„\æ$Œ1#\Ä\Øÿ\0»#\ô¤\õ—\ô8Ë’<G\0LU\ó\Ð)À\ÎÝ…Whb|–‘X’@:|AQC+w\"\á®AŒ±\Ö®\Çpù \Èî¸ŒK#:œÚ°*c\nm‘”˜\æ\Æt\õ;zu¡™6+dd†ŠL\õ`P\Ì\óøÁš|ƒ¸pÀ\õ\ÛS5Î¶^d§p\ÎY˜\ê§q™!Ù’ I^—\ë€3\ô¢\Å1\n•$ \äF$ ~£\ï\ì~µ]Š~-»m¬³;€p¬µ4\0¬Z\Õ\ÛN C±Œï‹ŸÒ”aK^\Ñ\'hc\Çlž\ß*U@\Þ`‘®\r‰»\ç\éJœaNe¡¶\ñj\\º,\áC£\0Ÿ\éRHc\È 1\ò\Ä`“¶v\Åg-\å\Ük¤iŽ0I\Ó&“\Ôï¨¶\õf%hLk#h™d•T»H\ÑN3\ë‘ü\Übˆ»5¶¶€A”¸*T\ã8Á¨‹]L?¼X\Ü¹\ï\Øý(Qq$œs˜Á\â#T\áY´\îJ¡$çƒŠC‰\\‘jJ\ä¶q\æY\Þü\Ñº3xüWv\Ó\ð\ë\õ•\ä\å„U©\Õ\ð·5A\÷\ÐüZ\ê\á?³n\â¶tSj\Ð\ñ`´V\ÜEM½\Ô>,+Ó\ð\õ±w4\\C‡\ÞZ\Ì4<ª¢\×–Sq/\"\ÄÚ½	+Ðœu®2Éœ\\Ko&T\ÝA?e`vy@Ez0_•N•\ì\èøÚœNÇ^/\ZµX\îE\ßŽ(ffP}¢Ø’!c±\\i\'U\Ð/YC”°]”’\Ì\0\ïœ\ï\ô\ç\ßg§—†\ñ\È-§P†y[†\ÎA	#8\ÐN\Ìþ¹¯Kgš\Ù\Ú^Z\Ç\0¿1Kn™\æÇ‘·n¿\nË†z\Ó/ˆ\ð8n\í\Ú\Ô]\Å\0\×‚@5å“³(*1¹\ïšÁûOmŸ\á-ÀDŽ\å\í\n‚Ž\Ï}aA\Û\ðžûj®®[‹K•\"&l\á\ãÀ9\î:\ï\ïþuÀý­»³XYZ©™-Ô³›p\ì\ê\á‚Ó± \06\îM5Û—D8?\Ú&\á\Ó-½\ê3X—‘˜@wW(²œ\êl\äd\à\ïƒ\ês[¼\×6\Ò\Ã4¨1¼]~‚?C\Ó\ô\ó\Ë³œxJÁt\Ïý¨å¤’\ê&c\ì\òmˆ# \Ü7|\ä\äw«À\æ\ã¼‰O\ÃX7²s]!\Ñ\áW\Ã4Y\Ü\á\Ûl\÷\éDKù)·§Y\ê-|‹¯Ä¹À\÷P y\îD\ëï“¨¸,<y\Û5\Ì\Å}’fm2!\0\é.\ÊC\ð«\×\ß\å[pq+h`U:Æµ`šF¿L¶/AŸZ‹\ßbŸ­– Á™	s,§N–aŸZüˆ¼‹®\0E\á\÷ (\04\à`yyP[ˆ\ÛecŒ©™ž¨û\×\'\0cúu¬¯´|C†M\Âøœ|IažIU„pÊ¬\ò\ò\Û\Çˆ³\ál\í¿Q¿MÌ¸œ2»98\É\0\à\ô5z\Þ\í\áx™\ãID\\\Ç	(&\"J\é/§#p(ƒ]\ß\Þ]Em¤“ \ég\Ô8\ß2O_J¯<\ÒÛ³\Ã,,$ƒ¨‚‡T•#¨\Ø\ïZ\êk¤,ç‹¬\Þ\â—S¬–w\Ö\ñ++G4H\öú”7„«u3¿é¶·Ù«HDWA”\ÊE\ËR®V h\ÎN\çaÓ¥q\Íw$ÇŸ+jvTRÐ¡\0ù\0+¢û,†^\'\éf\Ú\Í3ª‘–>†eO`—fºþz:´µµžFrJ\é%q\ïm\Æ)\ã\á°\ë%e%\Æ6,\n.þk¸¡\Íi. \ésq\ÆM($)	G?‘F{±šœQq¤m\\\Ëitœbc²ÀT2Œƒ×¯•iKÑ™\ãmâ”ª\îJÜ‡„:ƒ\îÞŠ/\å%\ã`\0\ÈO‡;m‚k>I¸™·3vÀ»G*.‹€4“\âSˆyw\ò¡G\ÄQdVš\âV\n\ä¢IjUÇœlÊkÀ”ÖŠ\êýØŠLu1²¡ß¯±ŸZv¸²‘Š\Ì9y] JŠ‡\ô\'|Ux¯-\îÎ˜Š\È\ØÎ™ e\ïøúgš\ÍL‰ h\ÖB\Ã¦…\ÏO]p]\nH¬rO³É¡ˆQ\"€c\É$\äh$TqÇ•Œ;\re\Æ{Š\Õy\"…€–\Úd‡Y\Æ\Ïq\å¶OÀ\Z®\ê\ñiie(®|.±\È¯%uü© \Ëo¨\ê\ânª]Y6\í‚\öœM1f‘\Ã#maÁ\Ç] ï·º­1»€“,¹Œ\ã\éN\Ûx—5]\æ³\î¯=¹šÜ¡‰\ë\âS\ñ\Ø\ÓA“Š£\rQ\Ç\"œgG0HtÂ®\ßJƒ\Ïr„\êYƒœ\ß\'®|[Vª\Ü0\Z-¸¬§\ê·\Ò\ó±\"Eú\ïJXfy´\Ø\ó\åF\ÎmŠ\ëˆ0\É\0;Ç‘\á½P\Æ\ö\Ö\î\ñg¾L½*\Ð\ÓÀŽÿ\0\õ£;\ãL\ãŸ‡µ*u\n3\ï†,z\äSd‡$Æ¹qù@ERM¢–±²$\ì\à\Â(\í\×-8\æL\ä\ç\Ï\ãÚµcD˜Ip$\Å•-d1!ALœ\É\Ø|\è¦>\Z\Å	ka\Z3]\ÑE¶N “Œ\õ\é\çY¦\Ëj˜k\ÉWUŽr’\Ô,­—sH\ÖIl<\ïY\ñž\"gt‚\Ô\ÜKa»\Ê\Ê“»aw\ï¾þU\×\ß=¬k(¶¸°Ukh¡i\æ4EPrÜ¶c«\'§\á\é\ë\\\ç±\áÖ‘H}i\'\Þ4¯øQ\Þ@\nªê‘¶À\Æ\Ø>}\ëdù\áFI~\ÖL\à¬/¨\Ùm\Þ\Þ1ƒº8Ï­+^\Ä\ÞXfB/5ef,\ä!³ù°»Ÿ_\×~‘/-nE\í\Z.”X&Ye“\Ë#7«Q1,V0\áA\n03\äv\ÏÊe6rwfH¸ÍŒ\Ò,FMvŽ\ê@Àa&\ÆN\ã¶þU\Ú\Úq¤I/˜¬§2P–\êQ†5ƒ\ç½cq\È\"x\ì\çy\0\ä\Ý…B¤Œ„*…¼:\\Œ\í¶;\Ó\\[\ðC­n²q\n˜D²1uq§.Š\0Q“\ÔcÎ°~³NšD¸—¼\âs\Ãä·%“O¾Nh€†\É\å¡Æ²¾\Ä|—s©Â¸7	µ\ÄQD\'“8k›Ÿ\Ç#œ€\Ø >½kœ’[h`‡Ául¨™Ÿ$¹,:€Û·¥j\Û_\Ï‘\ó€ŠDd\ì¯&dF\ç\'\êk´\rþoA\á³0¨Šd},Ë˜£Ý—L`o\Ø½\ë\Ã?™ƒjY\ìc`\Òb0É•_\Ì\ÞcjÝ¹¸–\åžb\é1•\Â\í–üY8\ÏCY7‰(\â\ÝI“Àm¢R&\ßP\É>¸8 u\ÛÖ(‡A4SHÈ\ÛI–\ÏÝ£9`|\ÜŸ>¿¥o\Ë\ìY4|RV·\æ\Ç,q˜\Ú9IŒ¨ü1NN\Ø\ñ\Ú\ÔYMmo‰f…9‘J\ñ£€»\÷cl\ç°\ßú\ówü,\Ï\Æ.V8¤x\ã+=\Â&´:4©Ó—:†O„œ\÷¬\õß¥e\"Šûˆ;\Û\ð›[©aT¹*	ü¿{+ƒ\Ô\ó­þ\ÌIÆ¼RI²´‚ 7Q·\÷\Ò.>K]w\Ö\ö‰m±]	\rµ¬1€ˆ\÷zœ¾ƒ\ãWn/dš .8|±oÌ¤\ÔÉœ5Á\É\íUÇ \ç<*Y\ð\Î\Ãm9\ö‚¼,\ósœ4š\Â}\Üo†2{\ÎkûN–	\Â\'X£\æ\È&¼M Tƒ\Ë+I €;\×GœQ\'Ž\Þ\Ù\Ë*£Ç©Q°0G·‘üB°þ\Ô	&¶µ™T(‚Y#p €\í&\áÀ>x\ï\çI\õ\àþ>\ô¹›øŽ\0\n;P:`\n\é¾\Ì^E\Ão\ß\Ú\õBn\ì\Ñ-Zd\"&W`Á‰?•±€F\Õ\ËjÕ6#nÙ¯G·N	\Çø5¶”g²‚B\ä¥Í«Â—‰À;wû\ô”\ë6ù>7•_†é¸ƒÄ’Ä¿z[bT±\Û\ðo?*^Z¡x\æš8¦P\nL\"0²±\ÛÂ²+û\ë™¼C\ì\óÁkÅ¤3\ðù‹K¨Ê­\ÄaB\ÎoLº±]0\Éo\'–Õ–#y^¯\ã\È#Î•=6\ï\òÛ“~œB\ô\ÆXbº\Ê,\ã$\ì3¿l\õ\Æ\ÕQ¬8CLñ¦¤™p\Ò\Ç’B·F\Ò\Ù\÷\Z®¶\Â\á\Ú[3yi\Í¬\ÐÊ©9³¨¬K9#§Ê-¯³Æ«qB\â+\Ã$“G+.\Ê\òI•+\ØŒfŸ+\Ôw²‰ail®g|H1¤¨MŽ\àäƒš±S¬Y“\ò\ê‘\ÊHdrOc[ûüUš\öüF5˜¤¤\"²\ï\nÇ‚p1\'†¾\r\×Š\ßZ¬\Ò\ÊH\Ñ\Êåº¬d\n):¯‡j3þˆž8Ô\Zfƒ\r,Zc\ß\0¦&\Üc\ÞhL\"\Í\Å\ÔX#R¤ø|G·•Q<Sˆ\Ã2’·¬›\Æb}L\Ç\n\àýEZN\'uH‹dKÆ€¨Û–0½\÷Úœ¢\ð\Íy™®Lg™s\"22“Ý†Œ\ã\à}\ô½‘/-£\ö> \ÒJ\î\í\ã‰2tR¦$¦:\ã†\Éø\ö²\Ñ\ÍŠt¶ ¤F…ORr¿£T\ôBmcKi¬ÄŒy²‹˜\âi0ß”8ù(3:\æ\ËÙ¼+\Ì\Ç8k¸WÕ©“ \ç5H^_,’9\ç¤Á\È~pd”\ç©ÕŒ`\ö>”I\ÍÅ«‰-\ænb\å`Y¢\ß?€sh\ëu\í	\÷\ï\"BH\n;¨\Ëc\'H\à†>„Š´ˆ¥c\Ä\Û\'T·º»ø\ÇZT\\}Ÿ\ßH\óif\r\ñ1šT@0\çšþ\íšK››†$?xÈ¸\ò\n„/Ò©½´`@N:ÊŒ\Çý\Ä\Ó	\'bK¶Ÿ2\Ç\ÅúšgŸ‚[× úW:\äoNK{¦,d–\Ý\"ˆ†H\Í\ãbY\õw:ˆ_ˆi\Zg\Î:zl1\ñª,B“–\Î<‡\ï]¬Í—­\î\î­%Y\í\å\Ó(V@\Î\×Ku\ZHÇ•tü7ˆ­\ì	JG<zD,‚3&G\âB¸\÷\r¿^+™ž„Ž˜8¢\Ë#\÷ä¼-«¨\ßlf´„½\í´o¡\ÝYÊº\ëÇ‹³L\îqš\È\â2/µZ š•c\ÊV\rm«Q\ëÿ\07É·\â<B\ÑeŽ9°rYyŒ‡\Í\ó]¨S4rn]\äs‚\îýKž§©¬ø\Æ4t±\ÞÚIu¸\'”\êNv8b|>\í\êÐ‰²B\Ü\Ì±§\\lÁI\Î\ëŸ\å\\bÇ\âÂŒw}Âˆ\Z\ãJ¢<ü rª\Â\ç®BƒT¢;yDÐ®„B_™£@\óû³ƒŸY¶X\îL1%\â‰Ù‚\éš&Yÿ\0\'8\Øÿ\0Z\á\ã¸\â\ÄÊ—W)—4\à›g?­/oà¹Ž\é\'‘§Œ0V‘™Áb¬3\Ó\ÓÒŸ¾\Ã\Ô\ãX,\Ã\É,ªLz€²@2I\é\çÛ½YˆZ]Gy$z wXÁ“H\Z‘N­\ÃÅ¾ý\÷¯,~/yÈŠ\é˜@’«N-~\í\æ@r·\é\ñý+«\á<n9y\0\ðË©L’ˆ\Ï\"pN\à\0ˆ¹\Ï\Ä\ìs\Ûlu‰\Ùk_Ad…!¸•¥\Ì\Ò™\ÊHúUT\Ò\ß\êl*\çN\"\Ø?<¬6H\÷b,Z1\"‡\Õçƒ¶~µjHølL¨#¶ºº+p\õ\Äw!*\ÌQ\Ô@\ÝN\ÝF\Ô)Ž++\Ùx0«\Égi\Z0N\0\nŸ\Z\õÁf=6\Øø`¢Í±g·¸ \Å\Z\Â\Ó.%\æ\ë\æ/M€\Â\÷\ß8\Î6¬\Þ9yn¶wÐ»jw‰,`!€\ò\ÏsM?\Ú[m3\Û\Û]C{pø\0BšW?…5ž\Õ\É]\ÉwqpHeiJ¼—¤yhA\ÔN‘Ó¡\'=\é\ñaŸJB&’E]\\Á©ü# þoJ·a\Ä\îø]\Ê\Ï˜hŽI\ðH ‚Qˆ\Û\ÓZ\ÙL\Ð3\Éw¥¤Œ\àÌ“\\2¶‚DJFÀ\í’{|\ö¬8oƒþ¢\áZ\åWq-üˆ‚7\ÊÄ¸OvsY\ä\ï§v¿\êRq;)D7–\ö’À‘È“C\Ú$®š\Ø8\Ö—\Û=>U‘Ž%\í)\Ó%›,„ø\Èc¶\çx\ö\É\í“\ß\ç‹{\ö¯,\ÖÖ¶\Âx0ª³IÁs’\à6n•\ãŠP/³\Ýt.´\òNùøV\é~œ\rþ†xÌŽ$/o\ÊP¦\ìÆ®\Ät\r4]®0ÿ\0\r\ñU\Þ\ÖÁÌ¾\Õku\ÊPa7á¥·M#¡n^°º¹\ä\ãq}\ÔL²¢FÅƒr1‡n\îÀ\ê\'\ËcVŽR>2u +J\Ò)Ó‘5\àŸ\õijÁq¤K”¾\Ïx®Ò¼w›\È\êz)”\ä€@W×½[W\ÒÂ“Il]•d’e\Z¶<Xr¹\ó¬‡\æ\ß*\\Šy &š\ÉnH•9\÷¸\ëMý«\Ã\áH­µ\É,q†k›yœJ…\âl¬Ã¾û0\÷oKÀ5\Ïrbž\Þ°AqrL.W„‚<¶\ÏJ¨\Öov\Ï(mW)\âh¢•&\Ê\÷\'p\äŽþ‰ª-\Å.%!\Öí…ºdIBr\Ø\à.Œ‘Ž›T×‹[¼G%Å„1\êa#[´6\óMŽ…UÊ–®3\åµP:j’s\0l„À)“\ê$?µ\r…\Â<@2»u†op\ÜQ®x\ï\ÙÆ…%¹kxQ ‰Ž™›|´Œ\ê§$\õÁ`=H\ØR<NÌ£M\åÅ°\ím5¬“1?\ä‘WNþY«M\Ô/EÅ­-\ô¯³V,Y!•\ð4ŒcH2©\\ùS¦.ƒ­•\ÜqG+«<SÄŒFfUß·aY¯\Æm&Šavcx\äˆ\Ä\Ñû\Ù]J\ê\0.Cc×­s1\Û\Ï\Z\åZM8\ÎU˜m\æ@4šüc]y‹Š¡(–\öÁT•P\à°\ÆTŸ­*å¹œ_ÿ\0qwþ\â~¦•.ÿ\0GÐ€f;\ì=Nhƒ’½\õ7»?Ò«sN\çù\ä8r­e ò¶­°1TX’qZ$FX\ç\Ü2(NËƒ„#¯N¿J¬\êtªgˆ\Ø‡•>¨\×rw\íùŠyU\È\ß\Â1\Ü\ã\ä(\"6;›x˜c\ä+{}2\ó\ÂM#1;…{\ÑQ[\0\ôAm±\ëPEP\Ø\\³\ç \õ«Q(O\Äudnuù\Z–\ç…%}%·r\Ä\ç%@9\Û;FgŸ¦p@£=\èn\ñ*+©\ì\"BICT˜\áFz};\Ör\ö[s ²Í«Â»(™úT“Å°\É\ÆOÂ¬GI€0\Ø9\Éœyû¨£\Z¿­\âˆ\\o \àkhdUky\åb\ð©·\Ñ\çî«“\âº\öU¸¶iQ\"˜C#4w6\ìu‚$*\è2¸={i\Ü|\ÇP3Í•$t\õø\É\'l\äu\Î};\Öm¶\és\è\Ñk»}\\*\â\Ú\Ê;[‹˜\Ê\÷+3!2ss¶#?*£Ä®¦¿r\×–\ñH,V°g¨ŠÆžýzû\óU\å™b\æÁ\Â\ï¹\óªFBIcÔ\ñ¶sZc/\Ò4\Ò\È—L	K)u*r\nÒŠút˜¾YYƒ+º³!Ž\à½^Gž\Ø\ÍEc2È l6\ZA\ò­ZS²E\Ö\çNc`\î\r—_LÀZ‚\Õd“3<Ž\Ù\ê\ìNžÀ\å³Qˆ\0¸\0)Q†-œNr*\õ°˜NÀŒ!le»\í\\\Ú\ß]\Z¥Ù¥\r o$½\Ò\ÃPR¸Á\é\ç¾~©`\"rcÖ§B)ˆxK\ï;\ó¹\ÏožRø”€K\È\ê2|#Q>¤\ãÿ\05z\Þ_+\">˜\Ë0\\\êb:œ\ë$\õG.‡™¶\\/\Å\ÃxlúVkR¬d‘s9mA@\ÒP¶8Û¿nú–\Ö<.$XÅ¡GÁ\ÃG®L\ã«k\Ô\Ý\rQQ*É˜ƒ2d\á \ás¾2v8\Ï~\×ufP\Ã-…\ntHKg+ù3\Ó=q\ÛÒ„Ø¡aøg	wv~lY\Î$e‹\Z\ÒB`qZ‹p¾&- Ì‹ \"Ft…`•p02\ô\ñ\Í‚ªÀ…W$i*S‚pv?\Ë\Ü\Í9b4d\0Í•it¶À \í\ï\Ç\òÓ¡Z^Â™ý†\ÙA>(T\0J…,@ZÅ’\ÔB³¡Œ*bü¤‹€F–ü@O§}\Ç}j\ÒFu…\0\äøJ†’@\Îw\Î{\÷\Î\Ø\×j’J$y(YÑžG\0.H\ÔQ@\Ô3§bzv\ò\Ï~Pui#PV`#k,\Ç\Ã!PøúUgh„x\äMy:¨([q‚\ç|~\ôIZ\'dA!g ¹i$E!\\\õy$\Â\í\×\'3Š§:ª³*°R¡e]µ‚u…\ïø»…\äL°\ÆFw\Û\Ó=\õ¡\ÈÀ³ª\à(a€„\ÜG\áÉ¨± )=\×Rû³Œ\àoBg#;}qŸ~\õ²D“\ñ\ðûï²·\ì)PyƒøÀm\ðÞ•8\"¨ü]\çj*\ãlŒy\ÏÒ«@\Ø~¿ZJ\î\Ý6ø\ì(Œi—5Æƒm#Ý‚~´‘\Ûe¿Ëµ2¢þl“EíŽµ=\"»eSr~~´7C\Óa\çƒW[;?†(,3‘œ\ô\ÍRÐ¸•\×J\ö\0ÿ\0E®@‹>¬ß°4	Ù™\Ê\ôQ\Ø~\ô-\Åoœ\'\Û2Ö¾‘j%iX\äœ\r\Ü\÷\÷U\ÐT\06\íT\í›\Â|µ\ÃF-Œ\õ=\Ï\ô\Íg¿ay\éPÁ¶\ï\ÜS†Á\Ü\íÔN\Õ_P\Û\ÈÓ‡$\ó\ß\ßQ¥À\Ã|‘\ç¿\ó Kpü”m\áÇ\r¦š‰\ÜmŽù5E˜–\'rrj\ñ‹\Ù\Z\Ü\ð3;9\Ô\Ç$üª‘\õø\Ó)Œ\æ¢\äglâ·Ê“V\ß\÷\Æ\Ø\ô£\Ä\àU\Î\ìI\È\È8\÷\ÕE\ËD\Éf;\ïÖ­ 	žø\ð\ã®Â³\Ùx/#(xJ”_\ì\ä`û\ë@Ï­\÷Aƒj:@¼ gcŒoµb—\Æ\äx‡‡\Ó\ãš4R~ûdt8>\ï\é\\¯4Þ›‹q\÷zBG^\"P\ñ`¬ùcmü\ê\ÄM\Ì\çQ%]‘b\Z²T¤…9À\ë±þ˜‰\'U$\é ž:}h\ð\ÊrLº\È%yj2@\íŒ\÷¨x\nu0I¥B³:;‡xˆe’w Žþb¯£²\éGžA\à*²\ë^B\é!À\Ó1\ê„û³\\\ÔWQ”\\ª«\Ê\ñ\óa#`ÁŠ3\õ\ó­F’TDŽcE!“*Ç–[G\r—lŒ:\÷\íQ\Äf£º¤²\ÆYCk®ÀV@¬Fq\×\â3L\÷l\Ú\Ï>\0\ö†b†6V–\Ò4\à\r]\òÜš¤$m&)c™•\ò)]¼X\Ð\Æ\à\í\éÖ–¹uœDM¶”P\Zi±fdV¶\r¿‰ŽN2N\Î³5\ÂÒ’š\"_¼y2Tas¨º®°\à:¡y01Â€…mN9Ê¬\Z\\Ï­E8\Î\0\0mQg^q\\\ÄU\Ð\Ölk†$\ÙJ\é\êz\ì}\ê«\Ý,ŽÌ’#$)p¤sHVfS®Nc€\Ø$\0¸\ÒNÝ¶-!¤DJ\Ï\Í%K4ƒHpPJª5m\Øç¿­?*D.j@e\Ó\Ê(\ÊZBCl4\é\ìp{\ãj91\áœ™J£›˜`yer\ñ•Í›^1¾\Ø-=<Y9’HI’Í’Yr$ÀÁ8\ÇBOlž¹\í\Ôj‘,8`G¼‚\Þ]@¡–\ÈåƒŸN•5„VmÎ•fJÿ\0U9Á\É\Ç\ÇÊ„Ï’[v8†2IúÖ°’d®N\ç\åýiP5ÿ\0›\ê?•*p@\ã?\ê5h¤mJ•\ZLt\nš\öÿ\0©R¬™ ¼ý\ÍBo\Ï\ï©P½\á˜{žù;\Ô\ö¥J»2s2Õ·o}ºŸ\õ~Ô©V:\ô\×>\r\ß\åS\0^\öý)R©(¯7\ä\÷7\ê*¿eÿ\0P¥Jº1áŽ½- \ZWaøþª«uo{R¥B\ôx\0\ò›\ö«\0\èJT«úi·C\ï(ú!\ï\âý\éR¨ú,»	<Î½zü\êLHY0q¦u`i=)R¨ûr\Ûe\Îú®c³ù¬Ä©\ô­\î\Ï-\å¬R±x\ã‰ã–F¢Š2°N	©Vlh¿\ÄcŽ?\ì\÷\ÝÄ…‘B±Syddo‚6\÷mOd‘µ½¬ŒŠdnc3±\ç0\Éc¿a\ò¥J—\ØÌ¥,\×)¨–\æI¨\çXÁÙ³Ö©]¢g\Þ>•Ö·“\"¶¥^k\×•*UH–(Uÿ\0‹iU^U´b= \r¹@\é\ÇL\÷¬~!³[\ãl\Ù\ð\ö8\Û% W$û\Î\æ•*\×>‰ø\Z4_\0\ZGr\Î4Œ;	.@-\ç\Ð|½*þ\å¿ú©Gÿ\0…M*Uh–T$\ä\îzùÒ¥J´ ÿ\Ù','2025-04-15 10:26:25',1,1),(5,'Home',_binary 'ÿ\Øÿ\à\0JFIF\0\0\0\0\0\0ÿ\Û\0C\0															\r\r%\Z%))%756\Z*2>-)0;!ÿ\Û\0C	,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,ÿÀ\0\0®A\"\0ÿ\Ä\0\0\0\0\0\0\0\0\0\0\0\0\0ÿ\Ä\0G\0!\01\"AQa2q‘#B¡±\Ñ\ð$3RSb’ÁTr“¢\á4C‚ƒ£\Ò\ñDc²\âÿ\Ä\0\0\0\0\0\0\0\0\0\0\0\0\0ÿ\Ä\04\0\0\0\0\0\0!1\"AQa‘¡±\ð2qÁ\ÑBR\á#\ñCÿ\Ú\0\0\0?\0ß \\\äÔ™A\õg8\ñ\ÔXþn\éÔ–0\0\ç\ò\é\Ã\ñ*`À‚W.\â\Ï\æ:A\ô\ëŽTp8\ëÅ³\Ä\ð\\s$3\õ=x\àŸs\ãª\÷¶pT\õ0r<`ýú¯2\Ý`6\0\Æwc¹=foÀYÙ”’<°\ë[2Ÿ8ý†OIn¨²\Êœ7þii-*D\Æ\í\n\Ä \Ì\ËB\Ë\ãžpHúýº}¥E *\í\ÆüƒŽ}¸\éLe\ä–D\È\áHGƒ\Óý8«x |‚:\Ò\Õ\Øvbbvu+\Þ\î\"5\ÆO\õL\ò\ã\'\Ü\õ\\úq\î3\Õ%ˆ\'\ß\ÏXArs:²\ÜbChv\'r>ütV{Y\Üp\Ìc\Ø{g¢Ô³.\ìs\Ïß£«ÀŠ»³\ê$g\ð:1r‚PYŒCaŒlE\nª\0ÏœˆE\Ç×Š\0úu,\õšX“5bK${ž«b\Ü\óÔ‰\ê²z‰c Û½›¨ø\òz“\Õ2H“\ô\ê\ã˜3\Ä\æ|gž†i¸$\ç©•ÿ\0\áa“\Õ\Ñ@\ÔAÏ¶z¿\ä\Ê2º8cb¤+\íP‘€Á[Ž<þfu­*Ùš9™\Òk\'lu%—¼Š\\o.ex\Ü\"–\Ú6¦Ož¶${\Ö_\âkú`I)\Ù\rWxŒ–I\Û`\ã*{ž¡\ä\öq\ï\Òú‡2\Ð\ô¯‹*\Ôþª4ù+Q·4Ÿ!N3Y\\I$\n\Ö	žG/\Ï.I\ô\äs\Îs\Ö_P\Ñ²NX%Y§™\Ç\Ë\ËK%UX\ËeI1‡¶\ÒÀú¿V>§5By ŽfP‚5­\"°Š6LH\ï6Á»*FT“\çÛ–\Ó\èzœ\Ò\ÅWS‰ÿ\0²\ãU²Z¸Ek6™\Ïp b$\nA#<\àŒ\ç\ê›1\Ú‡\Û\É‚\è_¥]+ÖŽš7y\áK\Ä\Ã+×›˜\åü$`ø\ç\ÈÁdm4úÖ¦¯»r\Å\Èë©“\æFži‚\Æ5w0¹\Ï\'>Þ\ËÚ¯\Ø4\ìOJJ\õ\êµ\È\Þi+M\í;#‘°µÃœ\î<½B·\Ä7¬$\ñ7\0\ÍZ’x^\ä•X\×\n\á\Æügny\ö\Îy*Û´\íÁƒ)‘™¹•\á§QMXw”1\ÃZ6,n +J\Ê	\ï’=þýfµ5«b\Å\Þ\ñº«bU£$\Ò\Ë\Î\Çu¥){QWq\'\'Ûž:*=JÍ™oEZ8\ÞÓ\ò˜Q‘\×c$\ãÓ·H<Ÿ#\È\áf£§\ê–\ÙVN\Ü]¹\ìÙ–Ï¡¤Š0‘\Ä\à\"Å¿<`}OE±™‡…s(»Tø˜\Öš´$\Ñ\éºaùhb«Øž…ž±wf\Ø\ÆP\0“\Ç~£)¬\ë\öµ#+\Î\ÑC]W\"¸\ôJ‰!•1¼–\É<ù\ç\ô\êJ\éi%–\Ì5\ë#$b1V\"¯>\Õ\Ø3–\õy<ÿ\0wl\ØšÒ†Ÿ©TL#\Èy>ý1^\ä\ì\ë\ä=&e–\÷\í\Ý\Ó\È\õ\õü¿˜\ÙlS†œWu!#qý@~9°ZI\çhÀ\ë?vÜ—-=»Ü…H\ã˜\×\Ø\Ë\ó\è%³jC,¬\Î\Ù\ã9!Gœ\Ñ	\ç’!m$³~A¹˜\à\ÏJ[as\Ì\Ô\ìý\nisf2\ßA\è?Ÿ9\æS,p	pÇ“\Ï\Ðxèª•o\ÜY\rj\òH•Á{3\0D1\'ø’Hý\ó\Ôb\Ó\ì9pÀ¯µ”¯ ƒƒyüº\Õ\ÒÓ¿³~¿¨\ê2–M6”’3F\Ò7§º\Ñgn0|t¥Œq\Öm\"5‡\'2sVwR$`NYA\Ù\Ç\ß\ï\ÕÚ\ÍKP\ÙfÜˆ\nD‘ \ÈB€?\ÇÛ©1¬T\Ýü\Ø\Üq€*?Ã¡ÀfI\n\Æv¨Œ\ç\Ò~‡¢Œu‹œ\ô•\ÃNÅ•–E\Î#R\Î\\ª\0¤žzƒV|FÕ€\'o¡\÷m\ô±\É tZ\ËdS¹YWt/*I<‘¡\îƒh\ãù~\Ý¯©Š,jŠN\é\í\ç\Ü\õlœœÊ•(¿\ö˜\óu\ÝKt?\ï\"þ½wSºSlý\0dl‚¤|c\'«Q¤Á\Ü@\÷\ép¯v\ëÁ\Ïn¬‚üSÄ’¤‘¼o’\Æ\ÊÑ¹RA\Ã/mµcûg<–Ÿ\î\â4ŽC\à\ã\Ý\0#¥PÎ»ü€x<‚ß£\ÖO¸ýzUÔƒ­\Ã	vÁ×…GÓ®¿^œ}z0¸”È…†ø\ô®\ÝP\à\ç“\È\É\éÏ£\ê:U‹ùˆ\ÇÛ£T\å^\êÃ¯36”cIP€\à}:opXû¤\ïx\n9Ï‘×‘HI\ÜsŒ\ñ\ä’?>›²\Öq\ÌFª«¨\àBš%!O¸\éf©:×‹ÀHÀq\Ñ\Í4¾K\ã\Ýj\"¡¥˜‚\ÇÁnN~Ã¡\Ô<Ct&¡²„/\ÆNI\îu\Ã?ùQ\Óú{\Ô\Î3Ÿ·I\èÓ±$\ç|Dß€\'ïŽ´Q®Ðª\0§V\Ã8ºm¹ij±\ÏR\ç¯8x[Œ\õ5§:¥G\\\Ì\ÞçŽ„”±\à0û\'\ôèŠ¹ƒgÄ±›!<ý~tpú·3?tvú³‚<Ÿ=^6\ÝI8\àJ¯<™ ûu\ÇýA\å\î:\ZKs…8\Ç\'#yP™-bŽ¦_!p¬Ê¥Š«Š@,@\ÈPO¹\ñú\õ—t?›\ßoN·“©³’Ç‡Ìƒ,\\\çº0x;[a\ô\éÉžÌ C°\çaœ>9\ë©[V·j:Û±5\Évm›º7˜Žþ\òHxXÁ\Ç<\ó\ç\ß\Ô\î©FLµ,\'\Òr\×\Â:p„Oº\Ì\rJ‘Æ¯3\ÞZ\ñI\Ë\09\Ï$\ô’1j¦\ÇP\íT6¥¤°V­^žP\0ú¼¢\ò7rº\Z\Ö\õH.KB¯\êýÙ¤S³\æ%\n7\Í&0_…„\çœg=WO»\òš,T\ì¯d\ê&m\Æ/šb#W(l.v\ã>\ÇÀ\ñe«´™]V›þ\Ë[]¨\êú†‚*•2g(¬Ù‰\Ñ\ZXK$²À†I	R=\õ<x¿A\ÓjW‰–\íy&fœLqÅµ³is“\àŒœ}¼U\êA]#E\Ù(û’\à±X†\ÔP£‘@\ÉÂ¨\Ë\à®?»¦\è\Ò\÷c/\Ô\ÎkQýC\Þ1J\æa\rfs\ÞYk\àˆÄ»W´¾IQÿ\07J5}IÚ¬\É3\Í\"XS^­j\ñ*\ËjfQs\ÇÔ’@ûž¼\Ô5!‚(¢iç±Jœ|MeÇ—sžy\'\Û\ó\ñ’\Ö ^\Åk\æùÛ¶-´f\'&¥xcÀx\ê¢\àmRv\çyêºR\é”{\Î$v~ŸQ\ÛV‘¿j“\ïÁ\òÇ—¾ª\ê\"\'2\Ùq>¶Þ\n\î\ÚtK\ÇmG»Ÿ~zIy¬9–g\'q,\íX\ç\ß\Õ\Ñ:ÁXŸ·\ò\'\ð–“o† \à\'w/\É\ñÖª—þ\ÌCb«<ÿ\04Õ ­1‡O«5Æ±(2´’(ÁÇ¶Y—jr¡\ñœ\Î\ãAÙ‹A5ƒ\Ó\Ïø\÷H‹JÐ¯\ê3IdUHU\Þy¤,!…UIÌŒs\í\Ï[M—Ã†M&­5z\ínÝ‚v­¹]¦F\Ë\í\Ü~€`}úYbþ \Ú}6¶j(u+f)m\È+I(š@\ÛDk“·À<x\ê:V‹\ñÆ˜Áb\nkšª\ñ.\íÀ2\Æ\Þp?.³Üµ¼³c\Ý6‘žƒ>ø\ÓJÐ¬KvÝ›.±,²N\é*…X%`w\'\ä\0¹\ã¬Ö±\ßy‹\Ïv9a‚S\rwš\Äd¬j|ÆŠx\ì½h5--.­{W.BÁwB—e’H\Æ=\'3\ã\î:\Îh5¡Ô¬^†QO4Å¥–(aW†8ù		\ÚT\È\ÎÛ¦4\ô³¾DS_¬§II{xX¼\ÅÆŠI&U/Rl\È »€3ú\õ.ÍƒN¼‰V fŽ)a#»“¶(\òp>½n\õ\r»\é\ñ¨il\nÑ•«^P^3#°]\î0ªq\ä\ñ\ÒmN´\ÃD\Å \Ì\ñ2\â8\ã\î6Cv£ˆ\0­Od\î3“?\Ô\ÚWm´®s\ë\ö`úv1\Ó\íX¿¨\\†š¢\ËØ¬‰	™8c!-ùz:^š¦©¸b®Š\òvÕ§šI\äL\ò=$\Ýú\õv›ªOFiY\â£\å]1Œ\òF\àzºäºœ”\r‰»0Ó’A=hÌ I#…ú\õeÓ¢\äžbÚŽ\Ö\Ö3Š\Ô*^¤ü`?\ÙGý\ìø	þ=wAü\Ñÿ\0|¿\ó\ñëº‰\é#\Ú5¿\ç\òŸc\×5úd5\Ú*,b!(œœŠ\ò“r<s•>®2xñž³šrZ«-\ZkjS¥IŠ\ÄKf\ç‘Ù¦¸\'NxãŒƒž¯}F\Z“ü\õ»%¹\rh\ì±Ù¥\Å,ldE\Ên*|}‡JþŽ¤\Z…$r²Xµµ–ºF\Ñ\ÕŒ†i\ãdÛ´¿\Ì:5Ve†#\Ö Ál¡Š8?\ÌXc Ž?O~\÷c×Œý¯^APHF\÷f•;pAüú(\Ò]\ØR¬QŽeŠN	‚ª¦QÀœd\àAüº¬-\àŒ}\Ïøuo\Ë?9\ö\Ï^ˆ¶ø\ã n¤9zÈ¬CË’O\æx\êÍ‘‘‚2>ýv1\ç=J@±B\ö&–( Q–’v\n ~}P˜@0%b­!z·¶€ch\ÇI_\â]&>Q\å–1 ‰\ä\Ú P\ÄdY}eO\×h~z¦OŒ4•X\Ú8f3lf2\"E\åiYv\ç\ëŒ\ã¢l°ùAw•ˆù‘WÀû\ñ\Ð\ã20\ô3\ð£\ß\ó\è	>)‚9dF£+*\õÁb)2\Ã.T)\àsýü{\Å\Z$¥\ÏúJ:F\\‰£D\Þw*\ö\Ð\î>¡žz\"+¯‹V5o\à\ÌsX6Ñ¾=¸Ž>\ÜtR\ä{ù=&\Z\íFºµ6\ÚÛ„sOP@K‘Ÿ#žqÔ \Ö\ô»\r*‰Jzûp³+lwÀ;X®H\'’?^:NHŒ¦\0À£@øf<}:\õ\Ð7œcsŽ’Y\ÖkQS½‚o1¬SG,sW‘Ÿ\Ø\ã\×\Ç$}3Â‹?\ê)UR\'i6\ï“\â3Tqž22<“\Ç8\èAI†\Ü&Š\ß\ËÖ½=ˆ¡ˆXpŠI\ðyý‡KSW\Ð{rÌ¶\Õ\Äq¬Œ#ŽRûX\à¥F}\ÏÀ\õ”\";6\Â\ÙšvØ¯bId’WG\õ‡B[ŽHüº$\Ç^¼²(\í´qÁ!Ž8°±…V%—Œžs\Ç-»Žf¦¶©N\äfJ’\ÆaRC<Ž¨F/*Nq\÷\ê)~„\ì5\Zc\'i€\÷\Ü{\õ\ó\÷¯.\Ý\È\ðl•™{]Á\Z\0þ±”#!|sƒ\ã\í\Õ\Í.¢Œ\"‚Ás¹k°8>y\êø\âg\Îoe«;;G\óT‘‰eEi•Šûm\ÏRG‡ov\Äý\Ä\á‹#…ˆ\03ø³\ë\×\Î\à[¶$x»ª­i0\Øi;*pO\æ<}ü\õS¦©±V\Ì	¹Ô’6·\r‘\ãÛ¯nqÀlH+^7°?¬úL—~¨\Z¾ø\Ý\ãŒ\0eeF8\õÈ¹\\ý·g¬¦¡b\ÂSOŽ[‡1¬\È\ÐWŽ9xxe1‘Ç¸\0œkSW0\ØfI\ËNBù3·ß““\ôÀ\÷>5DTH\Ô* \nª£\n£\è@\îY›,xˆ\ß\Ú\ÉZ\í dúù¸5j(bI\ä\ìc(‰FÚ±\Ê\Ù£Œ\ç\ò’p1žE$\ç\ë\àŸú¦¸\ï\Æ3\çŸ\çübÍ´\à`\òG\òH\àyÿ\0O|y\é\ÔÂŒ	\È\êÝ­m\ö™\",\É?Aút¾\ö \à\ÇZ¼bk2!xk–\Û\Z\"\ò\Öm¿…~¹\ç\Û\ÏEº´¨\0C\Êê›—\æ@x8\÷>?\â>:\Ìju/\Å\ÑVµ¥G\î¯fK×‡\ÌÝ›\'oy\ÙUJ¯T\r¼{\õ[•r•5W±°=ÿ\0!\ò\ëi\å³j=>Œ\í-RT¯Se+%¤Ác\rTR  `\Ý4\Õ)Y«J–ŽR©\Ìjdž%§^³±\ïÄ­°°\ØpÀ\ä’Å¼\ç\Î*\n\÷£\Ô*,¶D‰%\Ê\é<µ&\ï1BË¿aˆ\î8\\\ç\Ýmï¦›Z*0ÁV\Ým>Î¡Z(Ø«\ÃbI\Ë\ï34N2\ÈpŸ\0~¹.\Ñv,¥¹\ç8Ÿh\ì>ž„)§\\“\æ~:ƒ¤\Âj\îg\Ôo“hH¯<k$ˆƒ\ðúSlhpp¾\0Ç·[ŸŸ·ZJ\ôªiG\å\á¡\Z—½%xJC\â2v‹€¤Œ\ã\É\ëçšˆ‰5¢%‘[“·½;n\0s‚S§Z|S\Úd\Ó*ˆÝ9Žw°)n\Ûm»}ÿ\0L\ÙHj\Ðz	jn\Ûk“\Ö½¬\Ú\Õ\Í\õ0‰\'\ÙN1\÷•\Ü¤\Å@\Îz\ÐY¥\ñm\Z²?z\õ˜f6,45m|¤0d\ï=ÁYýœtºO‡mR\Ó+\ê’O+\Ì†(\ÐÁb\ÇkU\Ý\Æ3\çª\ëÚž\ÝMjmWTšÍš\õ¡5\"±a¾_{œ3l‘\ö³¦C\n(8\è\\‘Ãž¾ø›WN\ÔlÓ³ªY«yd™$‚Gw˜²þ\ï+³œÿ\0\Þè¯‡\ë\Åý©­‰X¡´6²®\Ð —*À\ã\ô¥\ç\ÒDUÞ¼µ\ëÛ%Ži½o(9› µÿ\0\ÇNZ\÷,«H\ÒÌ•O¬¹.¹Ý—\0ã§´Ï³P™xœ\×\õnì«˜\Âùû\Ã\õ­fÖ—%8hR†FŽ¬–\Å\Ë5WÙ¼g\ô=c\ìG\ñ%\ö‘\Ù A0¬ˆ¡@>¡\Û2\Ã?\\u«\Ô\é¥\Û\r]\Ûh“G°7Hd°Ž\'¡\á³\0;}1DÁ\ç\nG]\rTw®Á|\óC©¬T¬o\Ç<L¤z¤ÿ\0\ëmB™Ž\ä¬O\æ@þ½~©\ryfµ;ˆ+H\ñ\Ç\Ê!`¼r0?.´L>™\óûuMá‘‹m8\Î	\ÏÛ§†:|\ãÿ\0\ò6\Ï\Ý2ÿ\0!\ð¿ý¦\Ïþÿ\0\ó\×u¥ùH>²ÿ\0\Ì\Ýw^\öJý¿·\òo”ks\Æ•‚œLÖ¤I\ë?Í¥”N\ß\Í1#%¦<6\0ÁÁ\'¨\éZ¹-\Å\Í.¡v^\ô\"\r“Åšx]º##%\×\ê+\Õ6©\ÕÓ§kÛšY$bQ |r\á@;\'‚Ä¼lþ\È j\Çfþ$n3‡x_q¤ˆ7§‚3þx\å)g^þ“\íZ]¥:UKv\'†*\ñ‰¬>\è\ãmƒie\â]§ùr23Ï“üC§T1f7c(fNB±ŒL\\I\ö\ç\÷\ë§[M^]:9Í»*\ÑJ\Ó\Û0Â“+\ï¼3H\Ê\îÂlÏ¥ý‰´=2\Ò5x\Ú{¡¾,M\"l$\"¡Š	Û¸\î\ã\é\Æz\ÔZÑ½LQ¬±}huË«\Ît»+YT»\Ë+¤J¨n\âd\ô“ú\õ[|I¤!\Ý,­»j’ˆ‹\"\òrs\ð¿¿Û¬•\ÝBý–œY–wGq#WŽvh\Ðv\òX\ò \0\ç ?ƒ#mv+u\ô®\Ð\0\Æx\Ó#L˜\Ë„[\Ú\ì\'ƒ\ñš©þ(³$bH!«V9AŽ%±g¶´¸…‚¦O%sÿ\0\ï?fý‹\ò+\Ë4’# ±gtŒƒ$\0.#_§\n<ý:‚?6;«\0\ëÈD~7n“Ç¾?On–¼\î+Ø­$L©H60\ËÈŸ‹@ N«”©Kã¤±\ßc&h©&Ÿf%KI¢\Û\Ýj\òÉ¶h\n–!V`8>—\\Œ1\ð7p-¥µZ\Ôh0\õ´\Ñ<ý™‡Â¬¬…J\Û|py\\‘tFRK†F(òˆŠª\íT ©\Ü\ÉN=\Ç\r\Z4˜I…¼\Ã}ysÿ\0»\Êqø\çkg?\èE8;¼Œ« u\Û\æ \Õ\ÔšDUÙ¦\ïfD–2yeG\ánû{u=8i^†å§‰˜L\Æ\Åy\"\ô+\Æ\á\ÒhØ±\ç\ó\ç=Nµ\è´\È\çbI!µn\Â.WpR\Z\rÀn\Ü8yþ½&V™\ä¯\æ#’BdÝ–q*Fx\õ\æK)\ÈÌ¡Vxü\æ\Ûû.¸\Õe’9\Õ\ì<\ò\Ï*‡m\áYdbX<\ã\0\Ó šúÓ–Xt\ô®\ó<Eg˜\È\Í	\Ë2Ÿ¨Àb|\õ\nû:Œ\åÌ³X\Úû\Úg’}\ÄD€—\\\î g\ñ\ôúu-\òg\"U\õ\õqF«Q\0r|–\Æ\í\Ù?^’3\×1î£¦!.®°6»³†Z\ñcUol˜\Ðn\0Ny\ñ\ç©Z•¯2M°\÷[ÒªT\ïŒ¸\Ýúƒý:\èk\Î\æ\Ï}Q\á$¢E¡V`¼E·2\÷û\õ\rV\r>*Å™\ì&F\à@\Ý\Æp>¾=ú¨o%\È\ðœ@b¼6{16<s324H \çX.<}\Çß£\ê\Ú\ÂXœ\äEia%7*,…KpT\ã“íŸ¿T\Æú`š£\ÏN¨¯.wF² *7·Ÿ\ß\ô¥Š<\óüºH\"9h”\áŸ\È\n\0\ö| T°\ó—v^R\ñ¦\Å\Ö\í#QCŒ\ç«^¼\êcL¨`x\í¸;”1\óŸ\ß\Õ),\ÑH±–œº$\Ò\Ê\Ê\0\nrGiA\ç8\ç€z\ëf\ì[¥™¦Žy\ðŒž\â±UÀÁ9¿ÛŠ\ä\ôž\'ÃŸ8\Æ=:\ÓÈ–&y!`2Y»¬\Ä`œ\ñ\Æ?\È\ê\æŸJ¤¥Z\ÕHO \÷\'N\áú†\õ\Ç\éû\õ\Ök5H\ZOœ±ru’‘\ì\Ìg‰Ä‘4ƒn021\Î\ë\Æ~+—H=¶D>\ËJÿ\0\Û\\ÿ\0^—\ö¡Œn\Ê\Ôj¹w\ðút¼úšÖ”¾,I7ü5k\Ì\à±*«ùsÿ\0¨\Ò|I]?\ÕÒ²\Ã\×bX+.~˜%\çþOX€n\ÎR6šf‘\Âa\Û\Ô[\è:6ÎƒfµqbKT%\Ø\"h\ëL\ÒL¦V\ë€}\Ï\ïÔ‹º°\ìº\Ç\â?(\ò_Š\çÐºdC\Ü±aŽO\Õ\ã\í\ï\îO°|M}\ÆÓ¨\ÎÀ\"X G\ò\ä€q\ô:\Zî“§Ò’\Ì\"\Ôr´2»Óµ˜\êcw¿œ1\ïKm?OøCk\Ëm\ë\íŒ\Ä\îÍŒˆ}Y$d\ç\ÐŒ\òx\æÀZ\Ç†\öM=c;b;\Z»Jd25\éÙŠ\î6¯H¸\÷Uÿ\0„‹Ú©U«,cy\n\ãþ&8?·R»qr,db\\eDedl¿Û¨\é±g¯\Ãu²HþOB\é&I-´™²ºjNm\Ë0\Ý\'V·¥§\Ê\Ù\Û6\È\ê\Å\nÄ¯$¨Ð¯¨9o\óž6¶\ìX\Ôºv¥\\S\Õe\ïTJ\ò\Ç1¨co\áÈ¤ƒÀ\È\Ï;º\Çi\Ðf\å\î¼+\óÐ¹–6P\ñ¨“v\å,1‘Ž8éž«=d\Õ\ìÞ§ª¼¨\ÊÑ¤\Ó\Âd—¶\àI%H¯¿Ž3\Öf¶¾ø\àpGCùM\Þ\ÌJc31¬nþÑ¹\ZG2¡°Lk(þ&\î>I\õ’3\ïÑµ\ßS‰+Ì¶»_\Åh\ÖH»I\"¼`–\ÆsÏŸ\ð\émû,ZžK$´ÀÇ¿vA\ô(Q»9Çž\Íýœª\âj2Æ½‘-U\í˜Â´’g¹a\n\ó‘\Âø\é’\Ø%\Ê;°\õý\å\Ò[‚m0VešX­vž7\íÈ–\ËŽ4dL{˜h´¾wy\ä€\Ü1\Å0¡¨\ò†r20Ø“ž8\'\ÇKt»úk}\ë\Õ\Ä,]»+*F\í\Î\å\Ç4¥\ñL•\"\Ö\êÔˆ¤wDŒ„*\"Å‘·†\ãÇ·C}\Ø\Â\Æo\÷s(‰\ôºú\Ä\í&—¨š\Ö&Y(\Õ\Ø\ð\ö\Ð Ø¡wcN8\ÏZ-Ô¶\'Ö¬HT¬\ï\Ñ2º9Û–	F#\ÓÀ\ó\í\Ö\ÜuW´y\Ø\ÙJ\í\Üž´\ß	H¿X\ì^\Ê!*®¥œ\à•\ôÆ[¿¬‘\ç0¨ŠŽ\Ê\Ô(\ôý\Ä\Ð6\ãz¹#´«\ãýh\ÏC\Ã`€ýbŒ\ð?\áw5Ú¤\çO\ÔB\ç\éÜ‹«kFEZ€€Ob=ƒ®Š§\Ú\í>e\Ù‹S\ìûqŸúu\ã¢F¬\ò2ª(\Ë3pª>¤\ôqO°\ó\ÕNÕ™\ìWi\"Þ\'z\ïHJrÌ§\Ûçž›\ïŒ\Û\Za|\î—ÿ\0m©ÿ\08\ëº\ó\ZûgþUÿ\0\ñëº¯~a½fEÄ±]\Þ@D²lh\ä‘w‚I¤\ç\ÎI\ö\ð|t5Ç5¥H\÷°\Èv\í0\îz¾œ\Ø˜G¦\Çv„·\"H\ã’\â—(\áŸ\Ä\Ü\èKƒ\È\Ç8ý}>|½H(Îˆ\ë~\Ç{¸\ö«³§\ÊÂ¤`©f%¼’Nz\äU\×<N¨¯¬g \Çv%UŠ¶£7\ß4l9Xšm\ê\õ\ÑG<6r>ù8\re’¬„A$ƒ·\nŠ0ygz\Ìi\ÑMf\Äi¢*º;\Äkˆ¬Æ¨šC\É\Ë\ä‘\ìOZ+Wtú\Ï5dž¸c\Öuß»¼\Ç³\ã\ì¿[ú{Wn\É\ÔTÅ·A\â\Ý12•„\\¢$\'\ë#útEj‘9²\óV\ïD8$Ÿ€\\V29\ó\Ô!Ô—åšŸÍü©&2Ñ¨b¤ÿ\0_nº\rkG®\Ð\Å\n\ÈL1E™QW!ˆ3¸œ\äøú{uw¸”®œrc”˜‰Z]ý¶W]Ø›Œ©<<Œcß¤ZÌ¥V\ÓH\Íf»©v¢º\Æ\n\ö\ãT9 g9<ýºuZ\å)\â…\Õa†2gv\"nfŸod\òO9\ãŒY\ÍbiZ\Û\ÜO»\ÐdË“\á\ä\ò\ãžHa\ÖV¦\ÂXV&•H¸\Ý:ž)¦¥H{JH›\Î˜¢\äœq\Î?_†I\Z9’Aˆp\ÒwJç€˜onIÿ\0\õ\Ôb™%h\ê·rn\Ïm\ãXˆ»Šœ4­!\ÇÕ™Œ\ò\0\ðŠ²¼\ê]\àXD\Î\ð\ç´NÒˆ¼\ç\0\ô\Íz…Z\ò\æ+mD¿†J´S\\ayOks4rK(z\ãl>­¼€x\Ï]r	*W´’,¶\à‰aqµ•–%\Øg¶…\çø\êKR(\âZ°lj Š7XLRˆ¬…\Äg8•#ŽGž1\×_½\ì€\ÍÞši:Fa\n0vJ£\é»#vq~}Ox=Ž%NOX}\ÑF‚%ˆ³\á¥\ÃapxdÀû\ñ\ãÇ±\"\Ïn7žH\ã\Ç,\nŠ\"fg«+4Ì ‘·\ð‚}°G\à:Q¤‹m‰%‚\á3\è}\Òn;dP@\Èúþü›z\ðOhmTŽ-»K(1È“fÓƒƒOÓªYv\ã\Ä2Vvs+k66M\"³I\ó\0<Ž2\Å\ä\r\äŒG\ôÇ·C·ru^\óe%#\\\ìP€\0\Ç<s\ö\è\ë}ª1\×KI1¯$’¤Q@„‘…Ž\Í\Ç\0’O\Ø~n4`Q[r\í,§v\æ\nÈ£\n°<»9\ç\Ïz·µ(ƒj\É\â+Š¬“\\a„¡c\nX9\0qûýº\'\0´±<H$\íKfvB¬eYS\ñ\à‘ÿ\0¯M%†«Þ’•ì·ºsm²=—\ÎF1\÷\ê«r†´•á‹µ%h~ka\à˜,‚Cœcƒûc\î)\í™\'\'…\'¤¦“\Çvü°+ßœ4‘	>\ã0iˆ\Îr0G·œuS	­OZy¥y^¡ÀÝ™0PyÜ£\'žy\ãÛ©E.\ñ&\×p	Š:\ÄaŽ\Ø\Ã<\Ùw\ð3ÁÇŠ KRj+T=\î\á\ÞJ+\å=R¦C©oÇ··¸›YÔ‰=Ï¬§]ª–ª­e\"5Ž:\È@! w(\åp‘‘\ÖªÄ«&\äRû¤PU¤\ÃFP¯u\ô½@’š„’\î\n\ÒÆ‚1”VH\Ë¤“žx\Ï\ß\ô\éÃŽŸ\"¯kXì žÁJ©©\ËW¶ƒ“ü\ç,rF9û\ôŽš\ò7\ç \Ç\ÌN‚ºT›z\ó\õ‰tø;—td†2|P¬\Äz?_†ž\è(?´§‹ºB\î0\Æ+e\ØrG\çÓˆ\èW“PøFJu\Ò=\ò\Ä‚Tm4ÌXý|\ç©\ëT¢mR\ÌP:“¨YP	\nÙŠ«CÇ¸#\Ç\ï\Ö\ÛpG¾rih4³z\ò2¥M&\Í}U¬)+¥‹i\Z\ÔP\â\ëúºØG\Ûù	$¦Š‡\Ë\éU\"ˆ<HÝ…N\ë4µ\ãmŒÞ®G$}\É8ÝÕ¢‰«\êS¼\ò•t¸d¸\ä1\æ!\'«‚¼€>\Ù\ö\å¥\n‘\Ù\Ó`‘KJ\õÎ‡\Æ\ì3üY“\ËDCd\çx\É\ò\ã¬\ð\ì¯\É\ó\Ö\àšzZ}Ššü¬\ç¯a,\04Šœª±\'#\ë\í\Ò]2\öiª#\É,yUc\ä¹6cŽ¶\ß¦\Ô\Öb\n@Zµ\Åª–a½·9†	\ñ\ã\ó\éo\ÂM)LµZT±«\0‹	[D:£w&b1‚8Q\ô\êÕ¹\ö—SþC\è#Oƒ\Ø\â\Ð?ùŸ¬J\Ó,†®\×\ô}Njj$3\Ç.øuX<‘\à\ô›QZ¦Ô§\Ã$%°Áa™Ã¼L¬A\Þ6\ä\ày\ñç¯¬\Ñ[N‹²Ê³uÜÙœ‚\Ê$}y\÷\ë	\ñŸ­\Ö\Ô`i„{„b44bh”‰s\'\É\É\'¥\rùcºj\éi\Ê`}\ô˜»3H/K4\ì\Ò;\Í\ÒwC\óÖŸTøu¡\ô\ï—9]£\í´–…\0\È\ÌsŒn²—\Ø\r„ƒŽCm$\ÓXeH;\')eŒ\ï;°…\Æv¿\Ü{\ôþ\Õ!X\ÅEŽ\Õg³\\­+H\Ñ\Ôhw6R4 ¢/\Ðe³\Õie\Â\Ë\Ùe\ÚA\ôÀŽ‹\ÚLž\Í|Œ\Ùo\×{Ø±!UE¯Ÿ8)?~¯ÄŒ4\0X@Çœ®]\ß\Øt\Ë\áÿ\0—}CW{Nc&%pal2\È\ïøTŽªš1oE²\Ç1K\Â\ã9ü].\Ó\ÜY8\Ý`>„°<tj¹a2»N¢\ÚWL\ã3\è\Z^¤>f…k­°\ÇR\ô1\Ì\Ø\í\óCŒ}À\ôMoˆ`‰V0±¨ˆ<.y	\éŸ\ïc\ìJ\çû\Þ\"b‡\æB²’X:\ìfs\ï\ô\ê)¨¬•d8\Ü\és’vŒ~&*<ýZu:nad\ã\ôš7¬\÷•qž¾Lú)\Ô4—\Ë$Ý¶\Ú\n%¨\Þ[`@<s\Ï\å\Ö\"K\Ð\ÕÖ¬Øˆ‰’V	›8˜\âT\r/t6\ñ\ãŒ8	¤\Ô\æo›‚V\r’»Y€G€q\ÐsÌ†ieWŒ»c¹µvŽTgÁ\Ç\ô\ê–Ø¸\ðM\ê*až\ðM—\ö\Ío\÷ÿ\0\à¯ø\õ\Ýb;ÿ\0tÿ\0”u\Ý+\Þ<sbúG\ì¤t5ScK\Îl€T¨\ÈÁ*7dŸ\Û9\é{TEYŒ’G¦\Ç0\Ç*–B\ç\Ò2r\0ù\äý†x:E–‚G$Á+©we\09MÀ `2y\Èú~%²DvHÁ$dm2ƒ€w6Ñ\é\õ\é$qcM/MŠÉž_ŸHÀ‚e\í†@\È8\ñ\Î}\ñ~´U¨ü0J™,X•\Õß»3e$‘#R3)9“=º\Ê\é«QI&¡\Û*„½r–e@B\ìg‰Á$û~\Ím]\Ñ\âFZr$Ýž\Ïu\ÌÃ°\ç\ÉUq\Äc\0(\Îsžƒev¹!\\þ’§\ÖgLÅ‹-¦\ÙY\è\×›<ª’«Î¾‘\Z¢n#ŸË¥\ÉE\ËY°\Ê\òš\Z•œ’¨\ï#dB\í\Ý\äœŒ\ÕþŽ°Ç¡jA_Qž\Õ\Øä’¼JDj\ì[gq\0“\'>ù^\0\ã\ÏE¶©fiuš¶#I\ì\Ífw\ÌR!˜(T±\'ýž??´.¤*\íß’8–\ç“\0Ó«Uh«Xž[²šÁ6\Éþ£(‘2\Êm\Èoqíƒ“\×j\ð¼+:\Î\á¤h€\"g‘·\í=\Ó+\n ·°úx\Én‰+v–Ÿ5R%,Y\ä\Ä\îU²p©Â¦2\ÙÁ\ó\ç<W©SµunC\ój\Ô\á@\Âivmi	\í—\Ù)Œþ8Á\Ï\æ´3FEX^b›:”–>UQk­s:¤ÿ\04m†M }I\\\÷\äu~™S³]Ý¥†Ha¯?v7i;\ò\Ï#l\n‡\ð*\ã\ê\òs\í\Ä@ž±†*\Ð5¶P\âH\ç†\"ˆý‘3È©\Âdrsý\î\ëQ\ôø!E©\\\Å1M¨kŠ\å”wp€WytWa·W»;¹‰k4_‚Yø\Ó\â`Ž\Üa\Ê2²¡gŽ/qœŒ“œg\ËtL\öD¡¤\Ý\Zµxc¯\"B[\ÒHI,3ÿ\0(\á¸n@Á:\"\ÝDíŠ‹\\š¨’\Ãrd’*\ñ¬±`#+¶U¸\åG¶~øM[Oš”Ÿ.“\Ïb Z(Ë©\Æ\å1\à‚N@ œ\àp‘/-“<AÄ»F¡n\n\Ó4—‚3\Â\Ò)üL#\î~5„€pHÀ;}\ð3ž´U\Ö\Ée\öf¯\Ý[,\Ê\ðªI\ß\Z0’B…\0\ç\Ó\Ï\âÁU¦°­ù\ØR8l2W\î\ØrUX‘ P«‚Kx\ð9\ò\å]U ŽXÍ«\à)b™6±\È\É\ÎO\ç\Ñpn%×¦%sG-š\ðK\Û²\÷£Df\ÞH\È`ÃŒqœ\ãÛŽ9®„F\Ê\É\Ø\r¶2²\Úx…~\äq€\Ò+7¿Œ’|Ÿ·\\¶·\É4±™\åŠ\È\ä:\ÄDc½€8#‘\õÀ\én²\á\ô\Ý]\ä\ì+\n?\êAÌ‘¯q\äc‚rx\çŸ×¥-\ÈýL5u†p!Z¡šÚ¤(^zGjmLA\0—¢q‚O—\ç\Â\÷¹˜ ¤4]~VHV,Š¦¸m¸±fr9\ã$ýþ½YWD\Ñe©E¥\Óë»¼5C4q¼N¡\ã–n“‘\Ï\ß\õ\ë\Å\Ñ\"\à6»U\Z[\nMy\ì\n­…a¿ /\é\ÕK\nÀ\Ï\ß\ç®šØ‘ƒŸ¿\ÊA\õ=bhÁ\Ñ ®­%j‘|Åˆcf‘Üª°†ºø\É\Ës×‘É®\Ì\Äl\ÐÕ„©»|·ýl‡\nŒÑ€1\ö\ã\õ\Ç]lüG\é]F\Ç\ËCR¢·%VHe\Û$\ó$d¨\õq\÷\ê¶\Ô5˜7o\Õ~˜\r‡ý*:±\È\Ø»ž:ŠØ²\îÀ‡³J2‘–¿\ö‘~oû=\"4­¾tøgÂ¶\æg\îI9 \ãÕŸ<œ§Chµ=\"KAQ[šq\Ö~ZÌ¦NÚ‡Th\Ìa~\ãªþØ¨;¢\Ô\rbX\äŠO”ŸSV1¾w/\ðQ—žº-s\á\ØMuU\0µ\'½\"¡\0¨²<\Û\í\Ô\Ô\0f6)Á\ÇA\éùFJ\Ø*ULdg¯`\ÒkZ…E©e#\Ó\ÚM:A=q\Ý\Ò\'\Ë\âDGcŒI#¢týGSøR’i%¦\Ö>JP‰^9Q\Â\Ã	\r’Í€œ\ãŸ>½ ´P\ÃjÁ!.W\"©f$`v\õ<€O“\ÒGTš[<I\Z$AcGØ r[.©\é9û\õºo[Wƒ\ï›n\ÌjÕ•‡„\õÁsVÿ\0ü[ˆ\ÕNš\ñFr£UR\Ä\É\Åp_\ë\ÇER¡\ñmg¡n4«š\ÙqY] •\\’†\ØÀ\È=£\ë\Z†š\ÍZ8­·i$j\òÚŒ<¤„\ÜZ,¨-\ïÇ¿Z\ä[J\ìB\ë\0.A5\ï\ïoÙ˜wY¬5¶\×A\÷ú\ÍQ\ÙÀ\Ê\ç\à\"ßˆ\"†\Ó\ë!\år¨ˆ\ã$\Ê\ò.\âJŸNz]\ðìŽ°CREÞ†}Nh«Vh\ã’jªc’K\Ù	\'À\'ŽŒ\Ö,†]J’Ù™¢XÙ¯2	7´>˜ÜŽx\Îs\Ï\ëÒ¯‚Ì±\Ï=W•Ž.\Ã)†<K\Ó\\#Î€ƒ\ì\0\Ü?§\ÑX\÷\ê\Æ\ã\ÄŽÒ¡4•Ü¯8Cüÿ\03IRÅ¯\ì\ð\'\ÌkÙŒ\Û\à\ÏqÂ£rpþ&§¢Wa;?1\ó	#ÿ\0¹!\Ú\î\Ë#1\ö e‰ýºeRB¿3\à·Î¤jI½R88Û’q\ã¨MB˜¸\'M%a°•ŒSD¢»\ÆRW\Ü\Ø\r›ˆ\É\'\ä\õ®5=\ç‘\òšº\r\ÊFß¾“\äzŒ\Ã\ÔÚ˜‘\ÔÝ\Îx<ûteA\"B¬&‘w.@R„~x \õ\ÄPØˆ\ÒÁY]c\Õ\Ël\Ä^|(?Nz¢”¾U^\ÔsF\Ó+LD‹1’\Ç9\Æ}±\ÖýN,©X32\Ê\Íw²ÿ\0¨G~Á[g8Ïª(O\õÑ \ß!œ]ªvÏ¹\Ç{\r•\ä\0ŽyþþWŠu[&9\ã#%x\'9û\á³\Ñ5´¦š\Ô\Ò\ÌQ\÷šI8\Îqƒ\öI\0Ž²Ë¼Aƒ\Ë=»N^YT¼AÛŽy;~½F\ZX\Ø\ÃiVŒœ\ã\ÏLŽŸ7rhL²zF6¥ˆŸ».O†+Œu\í\Í\ë¾È­¤£bÈ˜\òs\õRpx\èˆJô¶®\ôa†D\êV‚XF±`CŠx\ÉZ\âqü@(\Ò\ì=úo©\"ÕƒO¯ªN\Ø@\ÛkÕŽ8À\òCHÏ“À\éLšƒB\õÄ½^o–š\Þ$…„\äH\ß\É\àÁ\ã¡R†²\Î\Ðfh3\âf£Kº:°µ\ÉÊ·\Ò Ý™¦o®f\Ã\Ç21¶…ˆ\'1\ÄC/\Óvþ½y.­+©C`\Ù\Þ\å±\ÔSŒde\É\é{SÔ‚	A£e$:\î)\Ï\Ü:£´­´¨,HÀ\Ü?\ë\ÑE\Öy˜6\Ð\é\á_™‡|\ã»«ÿ\0(ÿ\0» ûW¿Ý\Ýz\î­Þ¿¬°Qþ?8\Ò\ÜK\r©a‰B«¼–K¾\Î\ð\Û\Z1p\ò8#<\ñ\Ò\ä­Õ€‰¢\÷²\Éu1\"\ò\ZV\Æ\ßW>üq\õ\ã\é\õ¾\Ñ)\Åx\êAf@wI=¨\ÒINrF\á´<\Ï\ß9=o_Š¬\ß-¢EJ1eš\ÔU =\æ\ç+\å#g\ßù\ð9Ì«Q¸\íA™°ÝœÈ»¬l|\à¨\Ô\ïC¨.nžHq\Å5P\ÍÜ…‚+\í`}\\01\ä\õ·¡¡hq¬Ž”\å\Åh\\]‘\åiV3•`<\òûºù\ã|A\ñ‚£:\äFV0Dª¥©Jp\×l¿¾²\èW~\õ\Æ\õZZ\åŽO\÷>\Âk|<\ÐÚ…ªÖ‘¢‰$h\ÙeŒLB°	Ár\Ì9\î>½&…\Ýu\õ\Ú\ðˆ ‹e0¶DBRYC³ø;I\Æn>l\ÇQz¾u‡•œÏž­‹I\ÖgdX´ûŽ\Òn\ØL7\0’7c\Æ>\Ý\à}þq–‘\òÀŸA¹p\Îa\ï\ê:rVG/f»`m_T{2Û‰Xœdþ},±n‰”XmcOi\0*i¦•\".¨¯\Ú\ÆxãŒgž²\ñh:\ì\ÂJNa˜žGH\Ñ\×!w)c\ã‘×ƒE\Ô½’\Õ\ÖNìŸ[°WŒ\í!\ÙT€:¥A9o¤w\Ý\õÿ\0Qû\êzR\Æ\ñZ¿>°a«zL\Ê\Î\ä`\äd\ð6\ä\ã\ÇAµb$–\îNK×’\Ë\ÇL+\Øz\á•7%\Æ0pxþ\îI¦<O\n½ˆ;r<!¦P\Â8ã‘•L­¿h\Ï?—‘\ÓJÿ\0\rÁ;Z\Ú |¼0HS´\Ë43\ì\Z\r\Ù\Ç\Ç\×\ö\'r€g?	@ú|\ç\çü\Ëd\Ö\ôB\n«0gYV-\ÅT ø¶Œp@\Ç^G\ñ\rvŠ…\ÉZ¼‰$Mf\âpP–U\"8‡\ó\ÎO×£!øcFÊ«5\éJX’ˆ~\ä\n\r ¢¯¯«s\Ð_û54vf¯Û–À´°ªGf\àd“…\Î	c\ägQ\ä5ž?I\îú\ÑG\ß\ç\'Å¶ TJ\ÔU\ËnÔ§$\î\Ü\Çpý	uKüW©—I>[NWPB³F\îG9ÿ\0\âHG\ô\é½}‚)?)¥–K«ª¹·¶=Å‹–“‚\àx\ñ\õ\òúž™Bg´*\Õ\Ó\ãC\"%£\îd–yƒü§ûº¹D\Æsº¤…ù	…?k¸\Æ\õ#\'†1Tƒ\'œ\à’§üþ}Iµ‹gF˜\Ísµ\Ú\Ü\Ò\ÇYR>ÒŸ;\Õ\0\Ûú\ô\Û\â\Ô)J\ÍWS²Ž±\Â\îX‘wmO\ñ\çÛ¦º]H-hU`˜Z= E$…\Ò6\n\ì[)\äq\ÇJ\ê-§NãŒ\Ç\è-n6³1?]Tum]\Ñ\ÕY\ìÐ©Y\Üu!\ð\ß\Å3\ð\ç~\í\Í\ã\õ\Ø[¯¡Ö§H\Å\"[­\ÛDŒ+M\Úmª6\Æýº½´=‡ª	kS«g\ë˜\ÏJX~k#\áþ\ã\Û\á`gÏ—\à\Ö*†[\Çw+ Š%‘E\ÆU_p\È\ñƒ~Œ‡\à­?’Í¶>\áH?ÿ\0ÿ\0^Œ\Õ\å±B\ÜÕ«H\ÉGÓ‚\å\Ù\Æ\É\âš#–\ó\åW¥Ïªj\ñ¼\÷‹\Ø\ó\ÓkeŽ¡\ë\0\ä\Í>ø~¹Wx{…\'\æf‘\ó\ö*Ó¥\Ö~\Ñ\á¨~V1f\äÚ—b±iœ’W\Ù\Ú\Î\ÚI8\ñ\÷\èGº\\\í2œ‘\ÈbTþÍŽ|;>hÊ™–˜¬ÀÄ<]²ƒ‘•?–O×ª=\ÍJ›œJ½A\Æ\Îg5\ZT©MB½YZÁ\nbQ¹\ã\ÂyÇŽ³o©\Ã6\ÅY¹	\0}¹nþ,«\rZ©,HŠg·\ÞqÝ’F,ÿ\0À?N¡[\à¤\Ã%\é%‰Àcµ¤\0†$±Ë©NÒ¡½°\ã9\Ä\ÚKµD\ò™Š1˜ÞŒÁj[5›øû‚\ç¸1¼!\å~½}&\í1½SP\Ò\ß\r\è\âj\Ì9\Ú³r<x\÷û\ôo…¡‚JrA<Áª\ËªfŽ)	h\Î\á\Êmþ\î´au!´³E&ÂŸ¿*\ê?¿¬û{SOi0©§j‡1>§‰\Í\É\å¯FsW\ß\ZJdt\n\Ë;¨R‡\Éqút³\àY2‚(û‹Ý½?\Í\"5Ã¢G\Û*pSÀ\ç\Õ\ô\éÆ­8«^Ù³$°L v‰Œfb‡t{ B <ù\'¥¿\Æ#‰\'f\"8­\ê3\Í‰c0F£b$ŽN?O£ÝŸjþ#\Ó\"#Ú•5”\âfŽAŠoÄ¬\Ö#u!\È#øe,1‘ç¬†¤º\ê_\Ô\í\Í*\Ó?!\Í+@Îˆw;2Ã•À$Œý¾ýo\ô\é©\Ë\ZE¸¥f@²¥{H\êÄŒcbG><u\ó¹VF:„½©`¬,Ù£p\å#‰+¡<mO¹\èæ¹=s\ê%¨%›kqˆ«Xˆ\Ì*OmK…L\Ò	‘’vÅÏªU\æùXâ‘«UV01Žœ\ä\àƒ\Æ\ï8\é\×\ÄU”R\Ó\áX‘\ì%ˆ\ÐM–y¬™\×ÒŠ\0~:®®–Š\Ã\ïº3V‘¶¹1¹E$~¤t\æ×º\0ùAjkaq+\çúÅ’\Û\n`ˆ\Å\Æ\í‚\0\0\ä\ã\Îz–\í,\0W*\Äs´FpA\ñœƒ×“C5q¦€·ymÖˆnH\ä¹\çŽ@\êu6Ù–ªM^\ÛWg5\á\îÉƒ‚\Ø	–${\ôGÀ\äAVX\ðzÀ\ÜU,J¼ƒ\Ü¸}ýŽ:”	K\ö\å›tK\Ýl³pŠFI\ö\ë¦\Zx³$M\èQ.Ìº²2¦q§œû\õ)\êÔ¯-žÅ„–4Â«+$WS\êO·Q\å<\r˜\í´‹7t\ô\Õ~~\åLØ–W^\É\â&7ýz[Ü¸¬&dqn\Ã:FAy?\ÂÁ?¯F´4“H\íÁ­¬\Ç\Ó;\ÕnüA$p\ØÑ‹)?S\ÇH¥_R°“,ÁA\Ü0Û¾€ƒ\Ð+³˜\Ó\ØP[S\Õ4ä¾©bjLv\ËRc>0x=%žKÓ³Jb„‘\ê=¢F\ÑÇ±[\ó6œjK.\ß\ã,‘zÀ\Æ\á•ý\Ç@‰, m¹!†Óµ”\ñ\ô\è\è\æ-kƒ™>\í\Ï\÷gþ~»ªw\Øÿ\0aÿ\0§ø\õ\Ý˜¬\Ü|A®\÷ÌºvŸ\'\ð3²\å˜üLÿ\0\"?1þo\Ë\ñdY+LfT‰\Ø\Ç\ÂÈŠ\ÎYÀ>€ª|¯¶:µ…¦t!\äŽY™\n±\ÅË¶\ãÀüþø\÷\èZ“Ù‚75\óË‚H\Æ\å\0[dcý¯n’¢­£\"9ª·r˜Â˜…!Pºt6\ZX\Ã¤‰\Ûk	@\Â\àq\é 1\ó\õ61]µWµE««\×fs2<³0\\±pÀ\ãhÊƒÚ­=ReŠ;6\ì\Â^#j\É˜XVþ;À\å|«g\' ûx-™TJ¯-™mº\Ï\öh\ã)\í\ZŸP\áH\\…\Î}º¥¶\Ø\'¡´ZÈ‹N¯&\Ë\Åj¶¥f“¸ý\ÈA\ÈG\Êr3ÿ\0w«u}±\Âu<\×\Z¶;\rw¨\Î\Â\Ë\çÕ¿v\Ó\Ü\õZK••¬\Ïv$ný×™%h»Œ\0Œ\î\ápC·ÜŒr:„\öoŠ\ô\ZBUÄ±HÈ€ˆ»jÁ)»ÜŸ8\èŒ‰\\˜DmZµ\Ù`\ï=µ®“\×%·ùi•Y&lß–A$ŽW:N°Mbj¯0W‚²\×˜\ä±.U‹&\ö>9Ý‘\áG#o\Òþ\ÔjWJ\Å$ù™…„¬•;§h10›Ê©À\ñsc%Šun\\‘6<•$\Ü\òXi–h²‰ˆ\Üz†â¯Ÿ®\Üq»‰ «I\0ùÀb\ïÌ¯4R´&¹1²\ö—|i0´q³A<–ã¶U{5jÙž²Zš)=\â\æ\ò¨Ú¨Op˜ùo z±Vz„…\ë\Ç(‚¼<-\÷PØ°\Î\ÒE¼W|p3\ã\Ô¸\éc·§M ±^mª¶$.Ò‰˜¯nHT\òneq>\åzw¶[n%\Æúš\É]jÂ’\É$SY‰\"\r\é®XLw\í\Úq\ÉÛŸk\ê4—µNŒ’\ÏRic\Ë ·h²n%YW9a–\ä(g©5	–Æš–R\Ý\Þ\íYHIh¦…\å\÷•	x\à\0p3Á\Úp[74ê•£¦²Ï§°\Ü\î\âeQ´¶\àW`l\Æ?aŽŒ\ó\å-ƒ\Òb•\"]©;¬Œ°À\ÎUTl<¸fr\Ã\Ó\öúM7\ì°Nù!b\í£,N²¥»r6@\ã“\Î9üú*ü\ö`ž\Çú3Nhÿ\0e°Œ»»$vN\÷¨\r£\ð‘\È#=ºGIF¥\ó3Û–FSPG/zs\Z(\ïX\ò=8\\\çƒ\í\ä:š\ÉrI—\èe£,\0”@Ÿ>Œr\ì\Å\ë);›\ð\ãŽ0}ú¾Œ\õk\Õø_\æmµd›O–8\ä\ãÌ£\å\ð7P\ñ\ãp\Ç?~‡ø¦‹Lª½\ÄuKPGA€¨±\Ê\0Ç§¿>zâ®‹\ðÕ£^\'±NJ2A\Æ\ËÈ²,p„<a\Ø.ï²“\ö\é]BSY\ó?´Ú¥¶Vù\Í+|\Ìeƒÿ\0¥\Ç\É\r\\,v\çù¢ü\ôaùtD\÷\îX%2v\Æ·\ï\ì\ñÉ‡¨\é=zú\åz\ñ\ÇS¹fp\Ï4\í\"°K3J\ÛÜ¿q½>[\Æ1\ö\éü:\\\ö\ã\ç*Â“(\ô°°VHˆ\÷Ia¹ý?^±d^þ±úŽŸ¨þ#>\ßI_ý\"ˆ*WjšŽ ±\É¦Žª¸n„E½\Î\Ï=d\Ý\ã\÷uÇ‘–9ý³\×\Ðu}6\ÅzrWi¢²=™V2\ðX 1\ã<c’3\ÏYJ\ò\Ç;+R·bŠ20=™cû\õµ£¢Ô¯e\Äd(\Ô)m\È8>±ZÁ<\ãøuf—\éˆ\\©ü˜Œ^š\è°Y«iû\ÒÚ£\Õ\ÜFj2N\ï,n§k@¡Á\'9}³\Õ\ß\é–y\íØœyü2\È?L:\×\éZ)­4k\Ã3¼²ÙŽ†i‡¥Ç§ŽGœt\÷³oRV\íQL|\ÇüF^\Í£±bm±N¬,>ž°»\å1¤¿\ò\ð:nu+U‚£i\òC\ZÆ›f—½f&\ô\"¢’?^Ž\Ö\ô£r9\ëZ\ÊB’$5m\ÂÜ™YHÍˆ”`(<g#¥ƒW™\ñü¤\Í=$=\õH\ã\"1‚\É.\ÆS\ö\äÿ\0N³µ:!±A@pOŸ¬bZ¹Û¼Ž!5\õ)\í¶ ¿¦ •bw~bÄ„ÿ\0\åè·Š\ì\è\ñWP†B}&[X\ð8Ž=\ØýzBËªê½²úv\ZH‚hš\ä«<\Æ2rD\\\ã\õ\öýŽ¥¦\ê4‰\õ9ŒQ+\Ì\ðB¿Á!\ì/x¹À\ÇÛ¬¶­(`I\0ú` mWR1Ÿ~O\ïZ²*{º‰’n»;z \íŽ:&­\"Û¶Ax”\î\Çü.=@þ½Ub\Ýx\ã¹u°”?p‚Ê¢Mƒ,8¹\èhtÖ°7\\½zq!gX\Òg¯\0Œþ#¯·\×ß¥\Ü5V³–À¿I)±«\nI\Ù4->VY.s\"nf\ïL’‚ sÝwþqÒ›M%\Ë1À\â!\Z†Š7®]aÊ…‚ø\àþ^z¾I?±\õ	b¬„Uµ£Ú–C1An¶\0ry#\ÏIlÁû\åƒI/}\Ñ!LG1\0`Ÿ\'\'\Ï[ºKZÁ–9§++U\'h\æ4\Õu-MKHš\ô‘\â˜IjI¦†\ÄOO\ñD„Œg\Î	\ñÑ³<Z–·¥M\Ý!\é\Å?q\\H\ß2Á#\Æ\ÙÑ”Å’¤u‡–£L³2„†^\Ø8\Èg\ç;@\÷À\è­*]Z“Ö–¢¬Ô–Yb\r\ÜPÛ˜.þb:\Õ¨^\"\Æ\Ý\î‡\Þ\Òu)¾!]:ªG#¤s\ÛH-–X™&œ¹d(\0|w^\ñ‹)X\éÓ’MBU¿\Ñ\äI%\\\ÈwL¨\á\n\çÀ\È\Û×•> n¬Zº\Í\ò•»\ácR\Ë$R\È\ê„\ë¶A·’\ï\ïÁ“|Cª\Æ5;TfI\ô\ô’µ1%\õ\çpÁ‚¡xSž}\ñûŽ\Ê\Û\Ó\"«T\ägv­>’D1Ë¢INj\òÈ·¤ˆ²1\\.\÷\\¯7t¾ø£3ºTh¤B°+8H‹oUü1‚\ôý¤Š]kq\èÓ¬Þš[2/u¡•\öª\÷;ˆH ût¦\núe\è$gž/š/hlˆ\Õc_-,\Î\Ø8\÷\õDÚ¾DbÁn89—\èŸVÕ©\\›æ¦†\Ô2¬f2W·\'†$\ç9ý:KvƒÁn\ÅXlLO°9UÃ°\ó‚\ã¢g¹¥ÐŒ¥k\ÚY\"1\Ø\ìÊ•KyZU,y\çG\çÒ¸Mû,²¡u‹,\ZYj“´–\Û\É<\ð:=jÅ‹\ÄV\ÖP@\æM–úYv¡°­œc\ß\ÇT\æ\ÄmžßŽ}$6?n¬w¹+*\÷7\í]©½ˆ\ô-\Ñfšyd„€±¾\Ö#(Xc \í9\èø\æ,NzA¾f\Ïû·ÿ\0—®\èÿ\0\ívÿ\0uþŸþ]wD\Ø=`»Æ—\êL\rp,Mb\Ô\ØT(Š€¬\Ü\Ë\02IvŽ«Ð»ý\ÇX\ãŽTfC,2GElgøž\Ã9`<Œ\ôFª!ÿ\0E¯ Ž\n\õkK\Ý¥\ÈÎ…U#V\Ú\'\'\Î	þP:J„H¶¬Á\Â\Ñ3¤Œû\äB*\ãÓŒ†\çßŽzW’cž\ï1\ÕzÕ–Kvª\Ë)¨m2G\"®\ÝÃ¸v\"($\ÂúH\ñœc\ÒeMBµW¸\"\ëE¨o\ËbL ,Eb\Úw1\à\0œà¦¹R¤Ka+Ø«\îaq\Ý\íŒ<¨\ÒFH\0Œq»\Øù¶‡tµ\ÝJh€Y–8\á\ËCÀj=‚ˆFrJ\äsŒã¥­¨±‰dŒ™¤mE‘£ZA†hl\Ú(;Œ\ÈÒ³Â¡°†\Ç\ÑAt\ä\ÕcylMi¡x˜Øˆ+‰	\"qˆQ\Ûv\0\Ü0xú\õ\êVµ®KZG\ìÒŽZ¦:²Oj+fˆ1c»\ÛÕ¸Ÿ\æ\çœ)\Z”Mv+\Ã;\ì’«:83J˜±H\àp9\ó\à{©Çœ¨\Ì_NjÉ©Ø¸gQ#C:³§„•Ápû‚\ö°`Tc\':>¥\Ø-m…+LÉ°Xˆ\Å	6£»v…\ÆK3\é ù\É\ÕUUD%@\Ï)x!DT…Œ \É\ó“\÷çšª\ÐmA&\ÃJˆ®X\ÌÌ†vª\ãvq\É+\Ã\Øc’2«xšX‚8…-‰\Íû	jX’G”\Æ\Z«Æ½È™d\Ú[# `y\É>:69\Ò*\ô\åT+¼’$QDs,hÃ—\'\ÔA\ç \×\ÏH¥Z„\ß™j«b4\í·mUV8üsŒœc\Ø\èþ\ä\ðAng¨+Ô\Ê\öZf!#~\Ë\Æ9\Þ@Ÿn¡«‘\ÒwT«\òú›\ÚA’G-H¤\Ç\Ìÿ\0iMÌ¯^$\å`db&\ó\Æ[†Ze\ê?\']ª²Š{Y\íq9;R3\Êå½²}ù\ð—R\ìÞ«b5ûÀ‹s´h\Ð%d^@@%¤$®$~A=	,i\ÙúŠ\í—O½¹jT8e;‡¾AFB”W@aÇ”ú=™Ì¼&~\Ìø­\Z\ÃqVü9c\ê#Ÿ\ï\'*B\Ã–p4-\ñD3–0˜Õ”… \å³\çw\ìs\éÚ\è+\É\ò¢6(\Ê\ò,\ë^>\à‘²HçŒ\Ô+h\Ô§§\Æni‰±úuƒL\ó•Vb.€\Ü\äc\ë\î\Ù\è40_9©ý$‡\Ã\ò\êµi\Ãm\äHvÔ±\Z©\Ä\ÒDˆ\èfa\éV\ÎW‚ÍƒÂž´”´6„\Ç)µ;(7.} «<ý\É\'\ï\Ñ$’×±¼\Î\÷œã€ úø\Æ:\"4¹µ¨À;¶Œcú\õ«](£1w²\Ç	\àI\'\á\\ƒ€¶\ë\ßX\Ü\Æz‚ùs\×|³±&IXþDÿ\0×¯V¼\ìOýãž®\\	AQ‹¾R“-‚\Ý\Éq#v¤–I³¹F\ó\àý2\é\ÕpS¯‹·VÒ§¶¼~¤gú\ôl\àw’²®yc›+€B¨;†q\ö\ë\Ù`%–\"AÁF`Á€\Éq\ÏV]‹\È™\æ\ï‚x\ÐÞ¨\Ó;w\äŒr	Ã«{O=+µ%˜\Ö)T¸\íJ6$¤;‚sÏ\Ç\ë\Õ\ð\Ø\î\È\Ð\Ï\Ü\îŒRÃ·’3Œ\'û‡D \ã ÀŽ¤\Â!j‘–N\á‰\òzDˆ®BËº8\î\Û+\í\ÂF\ÒøM¾\Ü\ñÓµG¤(úlûg¤†’ûZ¶D‚\Ä\ÂIšT²\Çw ŒxÁú}ú\Î\ÖR\×WµO3GIj\ÕfXE\Z4˜µ:q±KS·^8\æ\åBq(U?A»Ï£­‰Rµ\Í\èWý\ÈÜ¾1\ÙooSB->;ºœ¿\Ûfù°¶$“²¨U A%S\Û\ð@üÁÀ\ç¯-\ëšZ×šKÒ»\ÏL­H]\ãBb_S»\áp3œýÓ®S¡µ\íÝO\ÚlSªE¯\õ™hœ\ØønJå˜™\õt‡	ø\ÌQ¬r8_|qþsÖŠ]\ä­±i“Æ²\ÚY¤\ÕT••W9>qž³\Ñ\éºl\ÖÕžikG\Þ\ršrH‚)*T.\â\Ø\ßÇ·ž:l4_ˆ^Z:„pJ³W\ô\È\ã\é42f3\÷ÿ\0§O\êtu7…º\äŸN¿ù\Ój›©_tU:®¡.™Ù®¢Z\óI$;U\Ø\ä¦\n±sŒ}:T‹q¬Lº´B\0[Nc\ÝbS	Ü«V\ÆXýO\ç§-©êµ¤Û«Q%F\Ðgª¦0£v2\è\Ùþc¡“V\Ó*Iv\Ý\ÉMFyJF»„p#HQ\ÛÓŒrH9ûpJ)4 @¼yc˜[-G9\rY\Éu\åE©\ð\öžµª4\ð×’gT—Qš\Ä\ì\"AÝ˜\Ð\09>Ü¨±GRšÅˆÈ»¨¥IdY\Ý\ÒWÂœ2áƒœ\ãÛž‹:¾»¬5ˆtz\Æ««Z \ôv\Ñ#u\îO`\áG€\ç?A\Ôt\ÝWYš§\È\Åý·v6OUxg¯B\â8\È2k\Ú#`ûu¡‰¹ø™ßÊ¦H‹$–²O%hÆž¿6Œ\ì\ös\ÉU‘º1(~‹»?\\{\ÕVX–\ìUjÉ¾¥‹k\Û(„9”v\Ë8¶:ú=mB*µx²cD\Õ\"^D¦U\Ú@û\ò}ú&iªªKv:qVi7°+F»2*!b\ã’qŽ:’{\Ì)u^\ä\æfoÙ©¤\Ü\Ð\ÖD\í‰l¦\è$/+6C¹9 >þx\ë\òNþ–9\Æs\è?ËoÛ«f³4\Ü1\nƒ\Â/\õ\èy\n\Ü\ó\àuàª¼(”gf\åŒ\êÑ¬\öaG9~_\'h\í¯©\ðFq\ÇÛ­[YÑ¬\é@º”°K[q‚¬\ô¡”\Ç\ð\Ç4\÷dc`ž\Ù\ãžH?¦:*£\÷¦HÄ74€\ç\Ê\à\õ\çM\Ø\÷J\Õn\Ìû\äL“\î\à«\0\"?N¹¬1\\\ì\0\õm®\ë\él3©\È8>@<\õ\Ý\Ä\Èÿ\0?\Ð\ôH<\Ï;©\õo\Üÿ\0]Ô»‹\öþw^ž5EŠ\×v,¢W‘!\èQtÿ\0\÷=µ\0ù9$s\ÉÀz_d;	’QeIfŠ2\Æ=Ã€}@žO<z?QŠ\Íi\Õ\ä=…†z\ð\í‹M\é‘û{G\á\\\ã\Î=\'<r™Vk\Ü\í\Æe\Û\rN\ë4£€Q	?\Ý\Ç8…U\ÆÀ\ÐÀÓ˜\ê\"²\Ãn¥xb\"¼¤‚\Ü\Ê\ó2\Å`”Uú\È9#n\ã“\õ\öM<T\áŠdgU.\ØYšT\ð\ÜÀ\Â=\É\êiG\âûY¯ŠI¥dŒ:\î	¾m­¹O\ós\ç\Ç2Z!X\×P\Ö\ô\Þ\äq¤A\"\ïZr=\òb\n3\ç\Ù\ö\'³\î<~\æ.*vÁ\Û\ñ‘¬\òT·,V$\ê\ö\âŽÁ©¶G¤²?u”*\ð#xRA\ó“\Ó~^+D*Y\îUŠÕ’\ò®\Ä2K˜\á\ß\ã9r>¸\ñ\âº\ðü>ùxkkz„¯.\÷j°˜agFeP7@Žþ—\×WI-\Ë[\á\èGr­7‘µ[f\Édye½d±\Ý\ôûx\éw$\ã\0>ftl\Øÿ\0ß¤PežÈ¾\ñ\÷­=ƒ,F6v´…Š\Z°Rœx\äx$e\ë\ÒK§Nk\Û,\íq5–Ÿ1°)(\ÜA1Œ~½=’ŠQª,v´Ú¬\òN‘CB¸Ž5þbZB›¿.?¿¥\Z\äz\å8[¼\Ödš&³¬“WÃ•\Ã·\Óß©¤\÷ŒXdùu†\ö>\írù2\Ø\ôI\ÐYŸTm.´½…\ífœe–-£h\à\ò~£\Þ\ÛC\á\äŽHmjÖ®´ˆ¢AQ€\Ë\à‰˜ý˜`~k\ôï“’ýšE’´\òpI@D£r‡>q\ï\ÖÆM>²<PÕ‚)+;WvX\×y\Ù\Ê6\ò7r¥OŸ~©ªuÒ‘\ÞO»\æ1F™-\åG\Æ\"¯bŒeü1•s&&´$‘†N–“\ä·\í\ÖpÑ–:\ßhR/\ñj\õj€\í$¢c¸Œ\ÂA\ó\×Ñ¹\"s‰32y<\ð$¾ÿ\01\ë/¯„\Ó\õ]Ye\Ä\r!Ó¯û\î†@T–þ\å\éz5‚\Ë6\Æy\çŸ\Ö6úm‰»>\ã;\áÊ±k\Z]Y­Úµ#Cš\íÉ„B\ä¶O#\ñ\ç§+_\á½\"X›·\ZXhÌ“O\ç#¶–\ã\Û\Öo\á\É%\Òu-{E\0„\á©\æ5Á(\ò°ÿ\0d&\ÖÇ¹\ë\Ö\Æ4™\Ý.L’?3H\ÃÉ‘\Ï$\ó\Ç^\Õ\ê\r\'.Äƒ\ÐŽ=\ó\Ô\×\Þ\ð£§RyŒi^Ž\îÙ£Y ±þ­’\ÌmB¿$}ýy%ÁsÞ¹*¶IX\Ð* E\'9\ÇIN$\ô¶2¼\äy?q\Õ\éj\êmr\ÍÀ\Û`>À\0\ê7~§=F›¶T¶Œz¢\nþ\Í e9Žâ•™\æ…\ðdˆ¡$oGW\Ç\ô=YžK­h\Ô\å‹\ç,\nSJ\Z$20’6	‚T²g\È\òž­ŸX\í\×ùš¿\'~0¢@°ZH\æ`FAE9Ó­¡bÙ‚§ƒ\ØS†‡w^U\óÙ—?\ñ2=_1\ÌL\Ã\ñGüUü\×\Õ\ÖKKøž\ë\ò¢Q¾–‹\Ï4\êý‚:ª*\çp<c£gø¯@\æ¯#\Û\î®\ätZ®ûIÁ(qÑŸ ŒJ(&9Ô“¹FvU$€Ž¤|‘€1Ï¿A­º\õceÀ\í˜\ÖB‘\ã+\êßƒ‘ãž’X\×þdÉ¨\ßTXV8\â¥*¸\çs:>%øf*\Î\ñW–[\"2\ZY;@\î?Ì‰+ž~œt\Ê\áW˜³©fã‰ mB\Û6\Å	\ç¡Nß©y¼\Ê:\Ï\ëº}«‹tÖ¬g\Ô\ïÙŒI:\Ì\Äop%Ü¬\Í\îTg¥O\ñ•t, ¦Ip\÷\ì‘@+\Éýú\ÏÄšü\ðº-Hb…Šÿ\0üBAÊ\öXý\ÇW}˜\âR°\Ù\é<«§[šý~\ñ’D˜\Ëb\ÚI**(\öE\ö>\0=k&³¢S\Ó\Íh¬CEUj\î&5°H °(@\Ï%ˆ\÷=|ú\í\ínu­5«2È¦a^7y²#i8<‘\÷\çÛ«VŽ–‘JÚŽ¡,U¤rD¨a”VB\Ódø\Îœûu—©¨Ù‚\ÍÇ J–)œ/>¤Çµ> \Ót\ë\Zœ–;FFx\â5bQ\Éb\æYpq\Æ6”\ó\È<\ôš½‰f\î\èzxU\n\Å\å¬l<A˜ä»³‘#\ë:[R\æ•J]I!©¹\ÚÌ¿)5ª\íijÄ¸UU\ß\å½\ñý*œ]¿,‘êš…™LM‘QŠQ…}D‡ƒ\ã\Ò?~¬k\ñ\÷Š¿Ÿ”ov\Í\÷ù\Èêº¾­e\ãŽ\Ý\ñfl\íX\â±\ÝX\óÇ¨B6~xs\ÑF;\æ\ßP‰\í\óºY#Ž	A\ê)¸\ä\ç ƒ\ë\ÏRŽ¼+´\ëµjÿ\01ŠXªÖ°\ì\ÈN\à\ó\Îù—\0\à‚8\èk‰HE\rF`\Ò=ˆ›Qº²I*¼\nrVÊ«¯\Üc<y\êÛŸ€\èQUk’ÜŸ)U]B\äu,\Âdµ\ó~Qj\ögx\ÑQ†vÎ‰\Ã\ðNúþZ“\ë±JÁ=u|4\ÍbW_\"E$#œn\ÏWI^ž¥¯”†ÂŠ\Ï\Îe„oPb„GµAR£\ÇGK\ð\ìÅ›²µš0ŒI‚\Ãÿ\0˜Ÿ×¥m\Õ\Ö0‚Fp~\ÄsM£³\ÄW8?ûŸ„Yw\ä/\ÖlH\ð\ßi#	YaŒ@£Á*am£J~ý©\Ë\ÌP¯°\Ã\Ø\06¨ýº}z£\òÁfa†	^`\ãe{‰1œ{u“±)ši¥\öf;Gü#\Ñ\ô\Ö+\Ô+¬­–\ì?Q\÷\÷\ÒW\Õ2rÊ¿NOVûtF•’\ì39Q,¹’9$@# ¨eŒg“Ñ³´1m¦\Æ<\äeDB‘Èª	+$c*OŸP\Ã^ª\"³,‘ŸøH¿ l7\õ\ëM*\è¬<M£“e\ä±b\"\îe>;pÇ…\0ù\ç¤\ÖjÖŽ@+X6N\í«ˆ$³œ`«gž…U\áú‚?8\Í\ÚBœ©È€vIÿ\0W,o\ö\Î\ÖýŸA„\ÉøÑ‡Ü‚øtMˆ\'±,	#\ð\Ë&~\à0ª\Ô\Ï³°9Sÿ\0\Ê\ÜtÀ`FbM^%\Ïù\Ç]\Ñ=\ë_\í\ö“ü:\î§\"Sl\Û]\Ð58\Z©¤$ž\ËS™lK,‘…Ž{2\æ&\ï\áAPy<ý±Ô´}S¯º6\Ô&¬–fut\ÓÛ€•F7´¸\î;@\Ç×­E\á\Åfl–‘#Ü¥€Àc\é_H\ã=[Z%Bc^D*)8\ÉØ¡‹Ì’O\\9\ík¶\à\ã\á:\å\ÐÒ‹´E2h\Zk\\\Ó\á”Ú´\n\ÉbVµaŸpŒ\ñ\Â\ãŒ\ã<{\ôÞ½:\ÍÉ¢­]²‰FÔ‚!\îFy “\Ï^V\ÍORr\ÔER²`{\Ì^?n¼´J\é—>§‰Áÿ\0\ëI´ÿ\0V}U\ÎB3)U¢µ\É[¦¡‡M¦œ\ó•\òÁv<}ú® «5°\0Úº~”€`c\0\Ì\Ø\èÀ@ p\è½ÿ\0H\Ô\×\Ý+i\Ë\àcýS·A\ï	,O\ß\"h\ò…[u+†¾v\0y;·)×¤Ÿ 5iNGO$/ŸfL\ãÿ\0/\õ\é\Í\ó¶´\Ïþ\Ã\×ùgŒ\ô6³›L\ÔTœv•l/ý\è\Ücþ½Eow}mïƒ½T\Â`\ãÜ±* *bfm\Û;g\nwy\È\ãÛ­\å+Kg\åm\ê«><ý.?cÿ\0—\í\×\ÏboU\ñ\Î\Zq\"e2F¤ÿ\0Qýz\Ô\è¸\Ó\î¡\Æ([‚\Ô^‡k§\ê~ýt¯VúCyŽ>?\îe\è[Vig\ß\Û/\Z†’#ÝI\Æüp\È1‘û}:]®SMKI¿cy’ýr=\Þ1½qùŒ×¦§*GØ\ë\Ðr\ÊjÃ©•Z‘½¨€\ã\nQ\å(?\">\Ç\í\×+¦$º\íë™­v±=108R\×\Âz\éo\õð®¨7ÿ\0\Û_¤-ù©F\ë|\ó	B@sŒ\Î7œ\ñÿ\0xý9\ö\ë#¯h\ÑC¦\ë–`uŽ¤v\êL*\Æ\"žEF\ßƒŸR¾#ùG<`)Y¿R	\íÙµ91©\Ù,\ÎcP=8\ßo§]§@º·6\0\È3;Oª5‚˜\É\ë6\Ö5m«-ØšU\Ï\ð\á\Ì\ò±HsýXt´üUP\Í\Z\Z\öEFm²\ÈÎˆQO»†$|¿þ¹n„\n¨<¨\àø\ñ×ø]¿\Ù\0Ÿ¸út\Å“§§€2}\ò–k,o›/Št\á>’–\ë“ad ‡¯*…f\\y„þ_—Chš…	\ôÄ’K¢›M×²fx\ÑU\0\Äo¹ø;‡˜?¯¿jF¾‰¬4a\á \ä¼y¶\Ç,{\öA•\àú²O><\óV©¨\ê´t\Í&}>=:…-F)š¼U \ß4i\Ü\ïiÌœ	ú\ô\Ïp6\÷N|ø‹ûO¼O8d~³x\È\óidµx¡®&YË³\È\ÆWEC“´.\Ñ\ö´­ ¦b¥vlÌ°£G½K*\ßÊ þ¬>½)\Ð\ëh\Z¥Y\ì\"ª\ÏÊ–\Ì\ê,¤|1`\ò\å†~_¡<±\Ú\×4\Çv‘4›\rZ›HÅŒud‘ß´3Ï‘Ÿ\Øx^°u‰\ìá®­\Ø\0:g\ôú³`\n\àd\Æ¤h\ó“UG\\ŒwZia†n†\Ó\ô\ï†nEt\×Ó¨0I\æ†hÚª‚DP2}Á ƒ‚z/QºhÓž\Î\Î\ãÆŒ\"RpŸÃ¼Žq\õ\ë; A%oQ›\æe‰£ž¥\â‘qb2dV\\ø?\ç\ÇK\öm–Y§w¹\ÎÁŒœ\ç?}MjŽ¡\0\ÌJ‘ˆµMYU\êE\Z£G›SCgÕ¸ª\àŸ ÀR=\ê–\î›RX×½%¹1´\ÅT¬A9\Ù‰?el{}\ØÁ¥EªN$‘Q\Ú}Z\õdYh\ÕJ@,31„ƒ\Î0?\ÎYišm\rNPT«[N‚­\È\á°\ñGß¹+ %\öØ”‚‘€A\ñ\çž7\ßV”®Xq\ë\÷\Ï\Ê)\Ý;r&^\Ôúµ\Øæ–µ8)C\n\Îd¹(ŒÉ’\\¶G8Â¯\ØuC\ÖÓª\ÇbWy,\ÛX’ey\÷\0LŠ0O<g\É\ÏZ„\Ó;\ËfN\óµ\ëzUUŒÁtB¬\ò\ÈÁ˜.…¯K¾&¥—X\"\ÊH\îLN\åYRU¹\Ésœ\ßAŽŠ5u›C¬\é\Ø\rÿ\0XžŒ\ëN\íKüi\0•6\ïG8l£\ã ŒAŸLRþ‰,®£NÍ‹­“”µ\Ûu\'Ý‹\Ï\äA\èÈ¾—P¡6®\òR€IRK‘T§[µ\n¤ –«gq8?~³A•r›G\á\à\àýzb«’ü\í\ê8?\ÄM‘ª\áº ¡¬|EB°ZW\ç«\ó^BŽ³þ\Ë\òÀ{\ã 5µ,\ÊYi,¾LÙ•Ý™;Ž\î:\\“,§c\ßN¸\âud“—Ž)d…ý\ÔÄ¥ÊŸøN\å\ÑÂŒ\æ±UÀ\é=ŠÅš“	«\Ê\ÑÈ§—Œ‚rA i\èüE^ÁH/£A\'ý¢¨-}\äŒr>\äg\òcâ—¸\È\ã$c?N­…K\ÍyÀwPq\ô\ó\ät¾§GV§\ñŽ}|\ã\Zmmºc”<zGºýÕ”¼‘\Êd¶•+6Ý»“–g\Ç\Ô\ó\í\ô\ë.z?VsßŽ!\Â\Å\ZŸÌ¸Ýž—½]kZ^‚E¶5Ž]ú™\'\Û\Ë´\Z<vjÓŸS§iV\Ô.LµI\Ê\ËM°\÷\çŸ?¶9\Ï\ã%\Î\ä~g­•ª´vE–¢ùjH–\"«E$m\é*…@lI>z¦ Pƒ\Ï\ç\î…Ñ½¬#;GÙž\Ý\Õ\ôKp$¶ ±vI5\æ»m^±\ç\ð2ÿ\0ÿ\0\Ë\ÖrU”• 9$9.\Û>ƒ\Ð3\ÔQ–2‘\\v°\È>\Ø?þúƒ$¶\ÅÁ\Ï\ás\ô\çª\ÕBÔ¥\ñ	~¤\ØÁ˜ˆE’\ò\íw²m\É\à¶%$ÿ\0‡?Ó¡y*À¤|ulN\Ñw>\Ñ\Î\Õr¿®GE\\‚^¼\Ñ\Í`ƒ¸2LT\í>}%qý\Ý\\0RC\'z¦Á\å\ç\ìß¸ÿ\0»¯ß®\èø‰fÿ\Ù','2025-04-15 10:26:40',1,1),(6,'Example',_binary 'ÿ\Øÿ\à\0JFIF\0\0\0\0\0\0ÿ\Û\0C\0															\r\r%\Z%))%756\Z*2>-)0;!ÿ\Û\0C	,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,ÿÀ\0\0´\0\"\0ÿ\Ä\0\0\0\0\0\0\0\0\0\0\0\0\0\0ÿ\Ä\0?\0\0\0\0!1A\"Qa2q‘¡#BR±Ábr\Ñ\á\ð$3C‚’c¢²\Â\ñÿ\Ä\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0ÿ\Ä\0+\0\0\0\0\0\0\0!1AQ\"23B‘Raq\Ñÿ\Ú\0\0\0?\0\çpÅ£\Ä\Èrt1À 95v\ZÔßž\Ä\ó«øm•`c¸È­\Ã\Ó\ÜB\÷	À³”¾\re%\Ê\Ä\æy\ÕÀ*\Û\é\ß\Ð\ÔcµX ý³\Å*á†¤p}N\Ô%Ý¼±’tùG&ŽŽ3´\Î\Ä+£D\Ò^C¥°s€3\÷5Ò¡¶·Íª¶¥9\ß}±¥\ì\õ#’ú\æ\'L±Teb\0\äWTŸ¥Ú²\ð\Ô6u)\ã\íT°9|‰~1z\ÏL\ñ\r’\Ä0Y›VÀw«k;\ßÂˆŸ0\0lýMt\ö\ð\Ä$¸8‰)\ãP?\Ý¦“ú\ÏÇ·N\ö\Ý2\Ö\Ú9•\ß\ñTv!€\\žûÖ TYÉ‘`ŠÃ§\Ø$\ê6–‹€ù¹™#$€ÁI\Î>\ÕW{q\Ðÿ\0\â>§Óš2«-\Ü$y†G\ïf¹•\í\í\ïQ¹ž\î\òwš\âv\×,Œ“·a·\ÐP¥{\ãÞƒg\Å\×`\ä\à\É\òŸ©Þ¾‚•g\ÒD\à´.²*ƒ\êPš`\êSD-™\ó*³.Oµ|\×kse2\\Y\Ü\Ïm:ä¤–\Ò<r\í\ó!˜\áø\×\â\ôHãº¾–\à\Çx‘\ÈÌ¾†ULÿ\0\åL×¦5TkS™\òs ™‘”\åK(>û\×B´,`W\ðÊ¸\ïMø¯¥\Éà´’i®¨dWu\'8\Ó $ûl+­t\ÉV\ê\Ò	‘ƒF\é•a\ßú\Öo\Ç#\Õ{+Œf\ÞS3y\Ø,\ñ€N\à“éš¯\êb)\ãqŒùAûŠ°œ(eA\ó+Å³2\çqZ7£¸*‚UHP	Š½(\\\Ã+¨RG4\ßm—Q\'z:c#\ç*FI«(\Ð\"…ü\éO\Ó[^|¢u¶0¦i4	4RDs‡R‡\èF\r\Ó-¬:|\rol‹\ZøŽ\ÅA;¶Ëþ€U•)X\ôþª\Ýw\âBn\ä[d¸¢NG\íL\n“À\ÇÚµm!€‚NARc4lfg\Ô\0X\ß\0r;š›8\ÍTÁP³f‰‘Ë’I$äœš³ˆ\ÊT™\0\ržZ¶\'‚9•pPh\Ë\Ë.­\ô©?ÎŒ¯ª\ä€w44×°E²°v\ôS>¦¬\0QÌ‚wu(~4¶–\ã¦)±¢@\Ï\êW\ÐP]\r£Š\Ò\ÕTn‹;\òFX_\É5ÿ\0ì€‹øW½U\Ú\Îd\n2€¶kT\ì¶oQ\Änµ\ã;,±”S­q\É¿\Õb\é\òGxÒŸ#jg;\äŒcjN¦ r8\ÅTu\'2\Ú\\®p40\Û\ÜU-ù\r\à*ˆEÓ…\É\Ì\ãS\"™\Âlž4š>]G\Óg\Ã]4\Ü:;¡\Ñ\È> mK\ñd|H\Ã#\Ø\×[ør\Ö(¬m\ò\0mŸZj\Õ (¢\\Y\Â\Ö\ÚJ1¬\Ò\îb@-ú\n\ÅE\Û\"µüDa†ãš¥;)\ÈS.P·bs~–Kr¨\\y‡\ò4\Í3AmŒ(\Ýr@SµS~­Ý«ªi@8\à\ïW¢ºA>_\é^b\ÍaL2ûœ©Î¤º»¶Œ³¶tû…2\\\ô=1\äh\ÉbŒ\Ä\ãp9\ÍÂŸÚ‡_+]\Ç\ï\ò+§\Ë#§.\Ð|c\åÞœ\Õjx\Ù>³*‹œ\æs\Ï\ì\Õþ%|yV8üÇ±\É\Åu«*\ÅnJü\Ø\Æœz\×2øX­ú‡T@1\âd\'\ÑY†+¤H«6I9\È\Û\ò­‹uµaŽOþHZ·g>ø²úhºm„‚VSus9UG\ÒpÎ¡Œœ’\ç\ë·5d:‹rÄž\äÓ—\öª®—o“\àˆnfE\í©¤\nG\è)089¿\ò&žÒœÒ­`\Ãg¥Nˆ\òpFG:¸5\àV:†‘¶\0;œl(„1q€¡sƒÀ<Z—\\1\ébC6]Ww\ß\ëF\Ý#l‚;vU\Ë(‚±\'¤qŠ\÷\Ã:\÷\\pŽ\ËÇ˜úÕ­—L\ê½GqCq™‰\Ô\Þúq\Çzh\éÿ\0#\Í\ÝÆ´lLi«œ	m\èF\Ða(f‰\ÏX2\È\\\é#G¡®\ã\ðoZ¹›£Fn\ì’\×LÒ‹h\ã+A€\Úü\Ün[\òª›„ú]¼¨Z4/ˆIc’KF\ã\Óz©ø²ÿ\0¨\ôè”AV\Ê\ñG2d:iÿ\0©nÀvo)\Ó#\èµ\Ïkm\ð\ð~\ÏÔ±£b’\ÜË®¥\ñÕ•ÿ\0ü¼fù\Ó(\Â9v\êsœ	0\ÄýB\ãÞ¬,¾;{•\Ôý$\"ŽYnÁQ\í¼y\ÏÚ¹ŸJ²“\'‡­—;\ñ’h\Óq9u\n!\ß@À8\ì;\ÕÔš\ÆÌPo\ØN·m\ñ/J¸hUüH iit˜²{kSüÀ«Ÿ1\Ë\õ®(/Y%CŒ\Ç&\Åvý)\ç\á\ëé¦ˆ\Ú\Ï#°U2Z»“\æŒl\Ñ\ç<­/9\ÃA¾˜•\ísn “ ß¹þT›\Ô~:\è\÷«þ)fy]­ü-•\Zy4E¤´¡˜\ã\'>\ÕE\ñ\Ç\Æ\ÒD#¥¾:”±ƒwp§\ÍeŒ„ÿ\0‘†\äþ\è>§\Ë\É\ÓT®\ï#$–vbK31\ä±\îh\à–\æ.T/¡\ßÿ\0j_\Þ3›ikac¥\Ø~&lz3\È~Iþµ‡ûDø\É£ß¤¤\à³\Ñ[n\Ã\Ã\0zQ’]´.06\â \ÔA\ö>\Ü\Z’3\Ü\àq\Ô\ê½ã§ºSø\ÙÜ²o,3–fh\Ç/NH\Å:$°\ÜÂ“Û¸x\ÛpA\Ï\Ûjù\î2\é\"²’H`A\Üc¸®“\ðWU’9’\Êg>\rÞ  ü±Ü \ì\Ã¥%n\ê%<G‹F\æ<\ä\çÞ¼1!\Ý\ÈÏ½M2ª\á†1U]B\ìÆ»g‘\Å-«³z\0¾\ä¢\í\ä\Âng\ÐÝª—©Nc°¸b\Ä~Í‰ü«\Ïø‡‹¥I\ÍU|EtÅ\ÛoÎ²«­·0¥†2\"µ¢©t=Ëƒ¿¹®—gs\á[Gƒ\ØW6°¥=ŠÓ gÇ¾Ø­=G$A\×Ô±ž\îF\ó\â„7\ÎYAcU\Ó\\¶0\Z…YIl“P•\äKŒ2K\0\ðv^ß{=Ý´k¨°úûUE¼ª\Þ™²q\õ£$½\é¬4$_Z\Çþ*\Æe-Ä±¥cr\0\Är\Ûv;bœn¯ã—§0F\æ6\ÛjU\ê+\Æ?‚4Š/Y[1ùŠ\ã\ß4ñ¦²ª©U8&U|:\Z;·+\óG\ë]¤ù³\ÇzQ\è.\â„\Äýw9¦[‹øm\ÓLƒcŽ\ôÅ¬–3‡hœûûBG”\ô\ë #y\í\Ø\ö\Z\ôÈ¿É©1À\õ®¯\Ö#±\êvw–\Ìwž3¡±¼s/™\ï·Ðš\ä¥YrT\é#\Üv§´L<{>¢—\Ëw\Ü\"68 Œ\çš5\0×¬ \0;\ÜT\ñ\ô.§\à,™³Bÿ\0Ëµ\Ê-\Ñ\Îø\Øûj\Íx–\òC\ÊCü¤¥°\õ.µ°ý„e\é7¨bX\ß ‘¿\ÛsL¶w,…¼¤£\nI\é¶\÷2¸	¥|\Î\Û\ãü+\Ü\Ó4E \Ã‹\Ï%ˆ\ì>´“Žxš\ô1\Û\ÏQ–\'\Ç/¶27Þ—~,\\[\ô\Û`Až~¢\î8Ÿ[m\Åf¿º1\Ç‰d‘\Z*¬—2ª‚\ÌÄœª±ûPZÑž\Æ+¥\÷5fbI,\ÒA6•vúŒù\×)Á\æ]\Æ\õ8•0\ÝG­¼\'\öK¨L\ã\÷”l[\ê\Ü\n.Xc¸È 1Ó€6\ÆFß\ÅP¡\nU=ÁŽø\ì*\÷§±`\ÎH\Æü\ñ¶\õFlÀª\Ä\Zk)\Â\rœd\\\ã~jþ¥EKY.F¨£[‰[\Ô4v\ò8ÿ\04þU:<.H€Œ\rG\Ó;oJ\ß\Í\âX\\§\ð7†¸\çit\çU\r¹€3v¡1GM\÷Vº¹¼™µKu<³J\íŸ3»b?¥Kwc%´J\0Á\Ò;žM\Ó\Ò4† Œœ\õ\Îù\÷£\ïb\ÕH\"´\r„ž)r{‰‡:¿Z™\".6ƒµK4\\\àw£l!R\Å1¾u/¾9•Ÿ1uLœH­m\Ì\ÊË­8úú\åL½&6ˆÛº\ä	4\'‚³Du/ÿ\0°5\\¶ÿ\0†»Œ\ä”?Q¸þ˜¦;X‚]$\nQq\élj_\æ~†’±\÷\õI¶=«=\ÂD\Ã:dEp\Ï\í˜³¾5\Z\î²@\äe3a\Í\òÁ*\ÅNE%\äE=\Â89\ÄE\0‰\0\ìOª‡¯\ÜjÒ›\ç#j\é\ð\Øt\à\Î\ØL’Oz\ç\Çh—¶\Éo¤É¥š@˜ÀÀ\Î(”Ü®ãˆ³¡UÌ§\é\ì+Zbüah\ÕY\ðÕª\Ü\ß[F\ñ\ê]\Ù\Æ6\0z\×[‹¤YhO\Ù 8\ì‹D´\å\ö“90$\Îc—c°\'\è\rj‘\Îd³“ì\å]Uz]²\î\õ2\ôøIEÿ\0\ÔWo¥vªrFuÁÁ8\ö5\ä\"9$g5Kø§\õÚ½,8$Pü&+¿˜\Êf·]$c+\È\íE\Çufê¥Š\í\ëJI;\æ\'zœ“Øý\ê‡Ož·’5¿\r«R¬8Ú¼º\ë\Ö7ŒŒ¶wR[£†9$ÔL#\ï‚I\ç\0“jŸ\â.$ù›¡\'¾I v…¤R\Ê_¸†ûR/S\æiÊ¤r\ÈQ™c#IŽ7¦fycŒD±Œ¨cû±\Ê¥>\ãŸ\÷²\×R·’9 Ÿ,`. \êýÃœ‘JÒ¢¤¯Ü³\ç1þ\É\íz\×Dµ\à\ÍÊ¤]&•hˆ\Øg\è\Þ\ÕW\Ô:}\ËY\Û\\:4W¤È—PJ¦0’‚r	*A \Õe\ì\Ý6o\ÏJ	4™£+˜f\Òr<D\ã\éV\ÝG«\Ü\õAˆ\Ò\âLÐ–Ì„\ä¹f\ß\Ø­©ã©¤-[\ò\îm\Ó\"\ð£$‘«N©Š\Ém\Ý0\ày\'qùRµ•\ÆN’FO5yHª¥[\ã4\î5Sb[¥¥Š]O\òº;\á\0!\0°@OoJ	z\ÏJ·Ž¼7\n\ò\ä¼‡—\ä;2°+\ö\æ‰Qp|\"\ç\à\ä«a\Ë}\Æ1K\ßŒ“ºŽ¾\n¨fe\ã\'\Û\'VQ¸+sšÔ°•f\Ù¥¹H\äü	y\Z2A-E¼\ñÀ\ÅgsÒœ,QN\Ù,ªp¤ø†\ÕiÐž]b›N†QX*W\ïV\ÝB~“\Ö*Æ±¼‚WbG‡œ\ápÛ‚~\õ\Ç±‚5\ßL°#¹—\Ã30+”r\Î{(À\öÞ”z\ê3\Û\0 •¸{g\Z¶b²;y‡z\éS\\t\äž(\çT:\ò\ð3`ŽqšHø ¿¾™¢“B\ð…\ÌD“’{nEB\à`\îbqg¥\Ù^5\á¶FÔ¥\ZF,7]<k\Æ\ê7)%\Ä2¼žf‡QƒŒ³½7|3m\Çuqª2e\ÙXF•\Î\Ùâ¼Š\É:½Õ³¬Y_©Œ9epG\×z?“,r%<8Q´\Åbë¬’U£r#8\ÏmŽû\ÑP\â#•q…uëœ¯\Ø\î*Ï¬t¨‘|H†AÛ¾itM%»i“/\ZO:jÀ\ï@ºš›\òŒ\÷.!WŒn¤:ø\Æ@Ï¯#\ì(Ž•~®7#P%$Cú7\Ðÿ\0Ÿ¥Wt»„s\á\ëÔ‡‡¾\Øÿ\0Ÿú\ïF\Æ\â\Ú\à\ÜÁ0n4žGÚ—´0\Ù\à0Žý•\Ö\î\ß;	gU\ß\æ$\ö5\åÅ·V·.\Ìcd}¾PpA\\\ço\÷\íY\ð\ÍØ•\ò599Õ9B[¿o\÷Š‹\âž¡\r\Î3w\0M&7\Â2r42·ži3^\ç\"M\Ä\r4›«\õ™•\'`NF\Ä\âªdŠ[‰Zi‹H\îw\'sQ™$šC#±fc—fÜŸsV\Ð<*Š‚4_‹\õ™¹.y1ƒ\á{;;|M\åœ‚O#\Ø\nyŠ\à\'°ÛŠ\æ‘uRU1¶~`6\Í_Á\ñ\r”K¡\ä°6\È\ÜR\éeµ9\È\î4°Á‰ržÃJ™&%\Â\ê\Ò\\ýoX&Õ‚û’3\õ ­/z\ÛN@º$¨eC`ûS)} Ò¬©œ	\Î$\à\ñ[)##J\ÇÂ1ùw­\×â›ˆOWW5:\Ì\0\0š›\n˜c5Á\Èq„KsÝ™A\ô\È\Õ\ö\Ð_¹†\á\'•Ld•FUe`vý <ý+R¨\Ù\È;\Ð\Ï\ô\îUÁd=\ö\ä\Z})Tÿ\0yÀ˜U¿V»·\ñP“\Ç\'ˆU_!RFi\àr­\×Rü]Š\Äù[¦‘\é_$±¨-¬Ÿ\\\à}¨B™¨œ‘¹\åX\ïØŒ\à\Ô\ì\Âlc0\ë;j±\È|\é\ò“\Ü\Ç\ÜU\å.\îƒpiaŒ:\÷Á#ƒ\õg\Ò:Š[\\\Æn0±\0¶ùO®7 \Û_\ôZ\0\Ò\Êx¦µ˜²\äsƒW\ö´°ª°\'Œ\àŒ¹Q\ñ/S\é‰?‚ž‰$_7†\Þ\" \÷\Ç½\ð\çG‚\ï§‰\î¯?`[­6\ó¼Eµ\ä¤`.Áv\ó`gs¸¡Õ§k‡F,Ô­\rŽ\ãq A6Bƒ…\ÛßŠ×©\ÛC\Ô\Â\ËK»’y#•q„{x‹Œnpk,a·[x%—\æ\àZ´§.<I\Ë\â\'bv\çÞŽVe\ëQ$¨CEg0/ ‰Ä’I\"Ÿü}\é\Êt>6\ÜÇ¨½\ß\"lBŠ82³¤\Ù/‰t4l¢úYIl€4\É\Üúþ“Áii\r\ÍÌŸˆ\å‹\"8r[I`ª\å9;úg~6¼`³#!\nYŒl¹Ü¸ùP\ð\ÇO\ÔB\r&<x``\ä\Ë–¯\óûž\Í-O\Éÿ\0ˆ­z«k\èÿ\0˜T·¹Šu °Œ¬~ML\Ù‹;¹\Üp\0™¤¡\r\ÔQ¨šÐ©r£¨Æš%9\ÛüU\Ó\Ê\ÕL±¬@)E\ñ~FV\Ï\0m\È4?P\é\ö\÷6\óG*‚¬r$fªt\Èh|\îÍ¹Œæ¶ª\ö\ñf9ghUÀ˜­3’|DD!6\Æ\ã9\ßò³»‚\Úkss\Æ[”\Ð,9!°\0,Xž?Z¨ž\Þn™\Ô\'‚EvŽ)YY@ Ir¬\õsSø~(\Ã=\ÂC&¨Ê¾½ÿ\0»Œ\ÖmÕ²œ4tî¬¸cˆ4S^ø-\r\áH\ÉWÁ\Ê\ñ‘ƒKw\åK¸Mù\Î*\ö\÷¨C9ql¤)\á˜c#\ØU$±\êÀv\Ø\ç½R®Oo9AÌŽ\Â\á­\Ø\ÞM\ÎÜ¨ùXùµ:?Q·ü-£Ï¥´¸·› ©_˜}F\r%UJ¢o\ÛnI54\ÓJ‘\Ç\å|¹\ð£\0Õ¬@\ç05\ØPc/Ã·©\ÜVé¤¸i°W“[—P¤	ÁÀß·½7uÎ‰\ÜB\å­\õ: Ž9mšEp H\÷M¾ƒüù\åŒv\ï\Z\\J%ˆ\ÆCG4Dj#Í‚6ûS\ïI\ë3I\0W™§t\Ø\Ë#¢\ÈR- \ê\É\0œ•\Æùß¿dnrF\Ó˜x¡yosb‘–Œ‚ø#V—\ão6œ®\ÜsC\Ç{\")\Ô6;\Õ\÷]¼¾\ê2Á\r\ÓIQ)h !B{¾TH\ã5H¶\â_5BÁFø«ù8\Ã\÷3\Ü\0ß‡SUŸ[Ÿ¥{q6’¥N\ã¤,À®\ã<\óQ\ÜhsœV\ò\ï‰^„ŽN¥:\ãI;b¬úo\ÄI4.F¯\r\Ã“\ô\Æ\ÕL F\'.?:.Û§IQ¶\ßb(–\Úg)oR-jTgr+@`\Ê~\ÕŽ gÆµI\õ`iÁ¢m€†¶“¾(	d\óÀ©\ZfTrO\0þ»PÜ’r~\ô\ÍŒ´©\ä\ÂÑ k\Ù q»\ÆD‹\ïŽGßŠ^ÛŽ7r*B\ãJL¤eÏº\÷\Í1/\å#8Ó¬QŒ\Ð\åCÁ¤™¸\ã&¦‹yˆ\ÜÆ’F>§\Ê?˜¬KD¾©2þFºt\Ò/<1’3\å\0\ä}ªŒ!\'NS¾™h˜6Œv\ã>3\ÎÙ¨“+nÁ$v¦.…\ñ,V)gm<7Nbm	43(‘SQ`ªp;ŸÖ¨¤+\æ\Ûbr}3Q\Ú({\ÈF6[ÿ\0Qš\å9…Cpg^·\ê6BÇ¨Ž\íU­\ÞIV\â\Ýt9E\Ô’?\Þ\ÜQK;\Í4‚\é\å\Ý\Ï\íAú1\É\Å*\Ú\õ+˜mf‚\Ó\ãFbi\Ì†(\÷\ã5u\ð\Ã\Ýth¦\ðve%È‹aQYú‹\ÞÑ†šºj’³øûŒ\á\Â\ÍP»\Ê\Ê\Û21¥\òÀ\ì1Š\Ò\Ùsu}$j<\ÚF»\nª\ã4,3ƒ5\Äù`¬Y´œyP)_.wÞ¤…\äV˜(\ó*±müÄ– ƒŽØ­ªj\ñ×‰u¾[3\êÏ™Bn¤\rœlÕŽ=\ê©\íaFvÀ’5ú¼\ÅPœj\0zÒ‡Søƒ¨Y\Ï#\rQ«Ë­ÎLP\íW\Ý+©~9\Ù@\'K3Clÿ\0>‘LI\ö¥\õWø1˜Æ“O\ç\ÎN =Y-2\ÞMl\Ó:F\Äxzu>R\ÛW3\êW6w’,°À\ñ2ùF§\nny\0W^\êV\Ò\Íˆ\ð@V9\Ç#W,\ë.k–V_\Ù\ÈK)g;ŠEu†\ÒCF\íÑŠ€+ …\ÉD\öµ›$m[$x`Vþu#O\Ü\Õ3Ìœdb\n’:œ6œ\Ç\ó\"¦DxŠOp¥\âbP”vûV‘jb\Älþ\õ±¸•£1F@\Z	‘t\éœqV\Æzƒ\Î\ß\ÚM<ª\ò¡µ?²—b·\÷‡¨«[c,0Bž#+\àüÀ¶ª»\n†¹$Vh“\Ó#foAV0\',I\'ÿ\0ûOi\ô\Ãp-¿QÁ¬®\"¸¶=>\ò0Ö¬\ÆH\äU=¬­·‹sþ%\Î\ãoLh–\Â\Ë\ñP±S fV#‚@\ÆG±\äTV£Ž\Û\n×¨um\ï\åûGi«~þ\Z\Ëi\Ô8\Êh¬\É*Ò†ùfI\õ\0t\ô›pj\æ\î\î\Ö\à0B3\Î;\Õ3G+\ë(F+>¥ÂŒÆ9È‘:8\0†ÞŠ†\â\â8\ÆÓšƒ\Î\08¨Ì›\é<QpHÁ”‰‚Rª\ã9\ã|\Ö*±}‰ßŠ1­\ò ˜ü\ã\æ#‚=\ê2…0\0\ÎÒ§p2’;²«_\Þ,	?N\Ô\ò3\ô£¯! [Ÿ\Ý*\ì	\à\àBr	$r)º¹@e}\Ï3Œv\ìkÍ†W\÷[#_J\ßI\àŽw­H8\ÇqÀ5y2H™´4D\î\ÒÂ‡\è¬7ü€©Y‰nsÿ\0q—\óSB‚\Ú\áq¶X\ê\õ¢½go“\ó–\äúWI„ù—#\'\æ­%\Õ\ÎOjŽL\ç9\î9&¶ •ú\×I‚K‘žjNœÊ·H[¸`3\ë\Íy*dPú]p\Ã=ˆ\" ŒŒJŽc½¤ŠÌ£¹\ÆÆœlqmm¨¸A#G\õ:\Ø\0úf¹twP\ÃmpÁ[Y};HC‚M6\ô;®«Ô¦†VPD{Á$BŠ\0ú³d’¦)AAú\õ¼*ƒ“\Z\ã*þ0\óÃ¢\Å9\Çý3$ªtglœ`}\ê\Â\×J5\Ðl;2œ°\ØHOŠ]pqû\ß\ï¸V\Ð;o\Ã\ÝN˜™¦eO<²»‡\Ë“¹\Æ(è‚£(DÒ³J\×ü\â0¹%\ò9b\0Õ«]\ëi!\ÌÎ¶†¨\Ø\ç×¹K\ñ7LŽ{9\çXÈ’œ\0J+¸Vv°\ÎN\ô/K±\ê]6\ÖCi{²øMm+F\Í©\Úu\ÇÞ›a\n\ñM$˜\"]E\ò6N\0ûU%\í³\ô»i:•‹:Ek¹¼³\ZLo,a\Õ\ò‘\Î3‚jª–°q\Î!\ô—-g\r\îoo\ñc¬¹\ê\Ý\Ã¾ic\â8$¹¸O ‘\ò\Ø\ï§l{sLw1\Û\õ;qwo/x˜’)bo$\ë$E0\îÇ¯q\ï-„¶7vislF\Ã\Äd\ä+c,¥{0\î+%(\òRÛŠ„NqœœiÚŒK5Q¸S•\ßHŠH\Ú\ê\Ñ2§\ÌcE\ÝO}\íJ\÷\Ë‹ŒJ¤\ÆTŒ2°\Û\óU;³ƒ%Jc \Ê>¢À¹8Õ½·&…·C$7L~S$)\ôµ6ÿ\0AD2\Ô\Ç:°Fþ½\èmf;H¡4\÷2±#²ª…8ü\ëfŠ¶(\Ý0¯·\È\ç{Fi±\ÖÄ±‘\Ùs\è;U\Ä¨H€EÀ<züª\Ò%Á\ïù\Ó\õŽ\"Ne•¨mT\Ýr\Âú^¡u,q3+È¡HaQP*½´\\”_\\\÷­\ä\ë’.˜Ê‡b¤\îpÔ—\É9UP±­f-$\é6‘F\ó\àm·µ©\á’2w«‹þ«%Ô™Â•\02@n\õX\ò	?„{ƒY\nI”\Óp£…‚M\Ù\ò\äŽheI‚°?Z³€—€\Ø\ÈolŠ\Î\ÉP\ZŠ§@%„rF\ÑÉ¡rN2s…¹­	[w\ÃœgÚ±^5„/”\Ã6ù­|Ec±\0|­¾>ø¤·7©N$EµJ¡Ž=8Q±\ê\Î\Ô\Z£\ìF±<û\õe%´sd\ã\r¡”\ò1Þ·N“r\ß+§\Ü\Z\ÒKUP	\"–c••…Ñ€8;ŒÒ¼(‡bGU\ôu¦‰`a6“ù0\Ç\ëZK\Ðú\ôc/\Ó\äaÜ¡ŠOÿ\0\Ï\éD¡\è\Ë\Zlˆ»2ª´x\',]ˆ\'8\rªs2ÿ\0uM{\Çw$R.‰ @’¡es\æÁý(DRVy«À‘%sœ\r¹¤S‘­G§¹;ý\r{Œ~\ñû\n\é\Ó\ÇÀJ\ß`«¹\íS\ÌWI*NûcÞŠ\éý=µ,\óŒcp§‘\î}ÿ\0\ßÒ¬@\îHR\Ç\\\ôˆ\ÝAc¤¤ M#b\ãV¢ ®\ô\à\ñ\õu[^“e‚¨¹uqk\ÙbU·\ÜK]”0¢\Ë\àª0+…;Ÿv\à\çši¶¾\êW#†\öÈ‰HÚ­\äf\ÛnA\÷¤-,NLÕ¤*®\Õ\îIÓ›¨Ë®>¡\Ò\ï\â\"PÚ¼¯€vl†ÕŸµ]k6\ñ+É@L§Ho|f†G\ë\n€\Éua¤\ãt‚uoþ\ò‘V±J<%Ô \êÇ™pFy\íC\ò\ã0»B\ò¨’\æ\ò4\ðž5Qv@O\'Q·úTsJ—\ËÌ«$3\'‡$YP’¡ý\Ö‘W\røy	\Øg\ßc½\0ý#¦xž4V\Ö\É.KX£Õ“¾A\Æj[QcpZr\ÓH\ço3X †(b@‰§…]!O ”º§O\ê=6\â\ã©t™¤…Ne™!8Ó\Ø\è>R½\ðF\ßNH’8´¾ús\æ\ô\Å\æ&\\VÔ¬=U†®®\ÓYÈ—¶•¹pbUŸ\Æ}R\Æfk‹h.#oú±®`\Õ\êFœ¨?øÐ½G­\Å\Ö\ä3~;v·\'\Ã\ÔVI\Ê7f˜*’m¶¥ùn#i\'ˆ‚|9dŒ\åUˆ\ÖG\"®Á‡¶ª„†\Ä\ól\îRa\Ç\älw \\awŽá§¹\å\Ü\Ö]]h•O™†?:—§G…_­\Ä\003- \\\ßù\Ñ\ñ\0M	Ús½6¢,\Æe[[{›“ÿ\0fe\÷s\åQù‘Jr]\È\Úu<d\ç¦˜ú\Â8\éÑ“\îB¾\ÔJ!e\ô\çò¥·³!f²¾¦J\å†B\çÚ±5¶†»o\Ô\ÒÒ©T\ÏÜ™^Xb\ñ\Þ7Uvp|2\ÃrqŸjŠI„\ì\òÃ±Ë…þ E\Å\ÂBÖž+~œ;Às\á™?ˆ)\ï[¶ \Ð\òP‰ZK£\Z\Ý iŽÀo\ëš\ÕØ•Ûš4nTt†\'ª<\ã\ß\'j\ZKgŽFS,y«‘\ö«\æT\ÉVA¶¢2\rA$ 1Œ’3\æú\ÔZÇ›±\éC¹9\ä\ïÀ\îjdÁ™}`\å£Sµ‘ùb˜-J#l\â–z–\á\Û?¥0\ÙÈ‹—“\ZT\ÌÄ€\0\÷+F\Ó\ÒF[A¤.\Üb·¼ºŠ¥–Cˆ\áG–O\ð ,jž;ë¹† A\\	%_Ú·º§\ïš\ÒH\Z\ä\å6C+±*GÓÒ–w4‰\È\âs™\îä¹¸º¹wI3ùv\'j\ñ\\o¹\Î=)ûþbIÍ´9$”¥N‡d¤‚1°\ÆoúS\ß\É_Bdÿ\0	\É\É18¥˜\â(\äsýÑ·\ç\ÅB\ê\Ï\ï0û\r¿Zw§\Ã\ZP\ì8¢8P}\ñ\ÍQµ,z…]\Ú.Zü9g–\Ò^E\Æ^O1\Ô(©zz)ÀQŽ\ß_Zd¶DmAÀ:¶\ÔR¾\é<8»¹·À\ÈPÁ¥o¤k–ý(!Œa«­\ê/Kh 67Û_c@B\òÙ»;¦¨¼Er¤•*3¾MO\'Ä-›Gq3±%T*Ä»z±$ÿ\0\õ¢¡°Ÿ¨¤S\ÜiHÙ‹xi!w*¹;“\ëOSC\ÙÁ™\×_Z~½\Æ.›yky\â¼d¤vþF3\Ôb\íŸ+9mÈ«$F‹Th\È\Ãr¾*’Ï¤Ù´†26’8\ö¢¢hWTw;\äGˆÿ\0\ZIý¥S\ä1\ÚËŸ\Å\Ç<Ia\0üº¤?MF¦‚\ñf.•´“Ž€>†‘\îúSeÁ\È\Ü9Àû\Õ!Žû¦\Îd‚FBt‚@?q½þ4¨Ê´\"üˆ\'\ò­‹üY„P·¶\Ñ\ÜC3@Ì“4n±øxÕ¬©\ÆmJ=3\â5¸\ÄS¾™€Á\ñ\'\êj\òË©¬¬\ò+a1\áGAm\òÍ“\Ø\ìú\ÖqFC†M%u±r¦ \õ/ƒºÕ§‰:šÌŒ\Ñ3\"|Äº`g\ñùU4¶]R\ØhHŠ†YaHÊ‘œ\ê_\ëŠ\ì\Ò\Þ@°O,¸)Jù\ôD-H¶ŽV\Ö\ÝNØŽ1\Çj\Ù\Ð1\Ôn\r\êb|‚.œ©_q–E\Ô{\÷¦8\ôÆ„`ƒû\Êr¿˜«[‹[+™a\Øp\ØÃ£®\ëU\Ò[½¤„\Ä\ä)\ÒA>a¤ÿ\0\î={ú\ÇMi(ry™f\Ð\ãK\Ô\íù~ugobª9$\÷\îh3\â\êtKQ,y\È\á\÷V\ìh\Ùf0„@Ì ‡f (\Ïvþ@ú\Ó@\ñ\Ä\ï˜?Už\ÝY\í&1©ºƒÇ‚EÃ˜\â-qø€‘–cz\n\ò\Ò;V˜Û™\Z\ÎF™ ™\ð?°Á\0m‘Á«~µf·–¬\\·\á\Ù|>šÿ\0ˆ‘šk•\É$si\íÁQ\ê0v\ïDn\'a23‰\Ñ\Ñcp\Æê¾§¾+\ÊÞ…l9›hÀ ƒª˜ƒ’‘\É …iuu\ÆJZ\'V„Œ«nPù±Ÿ¨\Íiˆ\Æ¾F ±PvÏ­y\ãÜœ3.~m\'ù\òÐ¹Ìž¤&\Î\ðT „Ù„\ÎI\ö­y,ˆÐ®@:\ÈÊ“\ÝjWŽ‹H\áu\È\\•\n\ÙÆ‘¿4F©ÁUrœø •LŸ>sDÌŒ\Å\×\È\íú“P†\Ã;v¢\Þ\ö\ã\Ã°M.3¼hÌ û·­ÃŸ6ÿ\0…ü\óB?MT\ê¡0$\Í\ì¥ý‹{J\Ã\ó\0\Õ\Ô‘\Z‘°#\éŸ\\P6½®C\â,–\èU°AI¢c‘·«(:\ð*da<:\Ø~G\'¥›¡¦\àƒ“,\à¸\\\01\òGZH6ûÿ\0•\0½>\â´¨\Ç\÷C£Ç¿\ÔdW’=\ÌZH$\ÇgŒxŠ1\êS\ÌRoC©\äMÔ£\å\ÊÊ‡“XfPrvü©}z‰l”\Ü`\ï\Û\ïQM\Ô\à@\Æi—¿–<»\ç\Ü-PV\ÆÜ f0½ú‚pA\ÛýŠ®¾\ëV¶j|i@r2±\'šFÿ\0\Ç;®)b\ç¬\Îú’Ø˜Œka™ˆ>„\ì>ßVxe‰rÅ™‰b\ÎrI=\Í2šom³Y\é%\ï\Ä=N\ä4v\î\Ö\Ñ‰¿j\Ãû\Ï\ÛíŠ¡“–v:œ\ç$\ä’OrNù¢ü5\í“\ïþ@V‚Œ\éÁþ#¹üø§B\õfg\å¤=¢K…2•Q¤\áŸ\åo¸§›>«k\Z§\Ô,j™TW„G‡?ojEhI\Î=\ÎÂ‡u(H4\Åv”.\Õ\î9=>\"øin`y/mÁC’\ñ-Á]@Ux\Ø¿µ/\Å\ß\nFÿ\0\Ä<]lAð ˜•ß–Ô£j\ä#‘SE\ò–”\Øv}\Ðz€Q\r\õœ…Ô”\Ó<bMC|ib\ô¥þ½s\Ð-£)5\är\Ü’R²\Í\ë‡*t\õ?j@ò‘“§\ê@¨\äÁ#Œc;zý«„	À$ž\îI]Ê\r	:Tn@\÷j¸\éŸ\\E¢\Çg@¤¤\å”\r€\'\ÐU­pE)e{\Ç\å®\ÃY\Ê\Ç{\î²\ÒÛ›$ø°«#©Îˆs–û\ñZxÁUS#lRœO	\ÈU?¥\ZEN\í}\èúP”)\Ü©\Þ\öúŒB\\•\×?–\õ“¯Š¡F\ÄE1rU#\õª`¿‰ˆ\Ã\rùÿ\0LÕ¼RDQ\Ü*”)¨:Gs\éZ\Ã‰R°‹fŽHnrD\Z\Å\Ã\á\Ñ>\ß\Ó\ó5Ì®\ò±\ó;j:q‘\í¿§jŽ\æ\ágd1G\Z„†={ª€H^\ç¿úW‘–™> .=\ê\à\ÊolRd{{¡\âD\çv›\çŽ@¬H\ÊARq8\ÛqµV5£Y\Ü:\Ý\Å,•\rc\âG(l\âD8I\í\ô\ã\"§†e‰•\'QUs¨\î\ä\æ¬\ç’Ó©@†\à–yØ±R¯™\îŒ1\Ûü\Èk4\ÞA¹{\é®\Ù\ÃuY\\’\ç•\Ð\å9ùO§Þ½×¨¢®\ì\Ç}°qž*=R\Æ\Ï\Z³$ ¸{ƒ,§z\ÚŒ\Â2Ád\Æƒ„$Œ\ì{c½bc™ H\õ$y¸U\ÒN¬o¸\ç*	J\Ø`x‡\Ô(¾B	\ÆIüY\Ü\ä{W€\áU#\'\"di7m³UœmŠTŒ\"¶£€\0}¨˜\çŠO”Mˆ?Ê†WÀÀ\ãb°\ÛDü¨\Ï:—\ÊG\Ýkr\',ŽÝ»\Ô\Ê\ÊyØž\õC$·–¬\ì\ÓZ–\Z›þ\ìc\Ô\ã‘F\Çr’a²­¸*v#±\Ý\Î\Î%€X\É\ß\'œÿ\0>\Õ\áUBspq\ò(/\Ä`\n\ð\ÛoZÄ»\ÜF\ÇË¥¿¥v\'n\Þ\Øtû\Ô\"h“\ÚG˜\ä±Ö¿Ö—\åøx_ƒºpÀ\ð\î6q\Ø2`þ†™\ÞE\nHn\Ø\ßÇ½\Ò*a·9;œ\çÚ«±O©;\ÌOžÃª\Û\ê\ñ-\Ù\Õyx‘/›\ô¡ÁmÁR1±\Ê¾¸§’\ÆLn	\Ød`ü\èk›;¢VhÀnÇ–A\îwªšþ¥…ŸqMO`G\Ø`þµ\é\ç%¿\Ï\ó£®úX·{\n[§Ê\æ ‰¬ŒÀo‚@\Î\ö\ÜG¶¾@(­­™F0\È\Ü\ç‡´\Ân\"\È3\Ï\óªû‚U£Z\Ým«J\ê\0[¶@A/N¹ ’­\ì{“\é½v\Ó ‘+\"¤Sš\Ü\Å$e\âx¼\ç\å,Xcø{\Z‰p	É©SŽ%fN¥1‚3¿¿\ô­).\â¶#H\åŽ\Øþu´Ë„R\ÊB‘\õÑR‹Ü1\ï\ô5³ *\Þ\Ã5\Zž(… ƒ¿;W™ƒ H\õsSu\ìs\ô¨\Ó*H9\ïÚˆV5\n\æ&INpGsÚŒˆ´ÀÃ°\n\r²[nN;\ÖÚ³ÀÛµ2¸v$\É\Õ\áˆžø\É9­\ÒId$,`gT›d…©¨•\ó7a“ž\ÔtK¥Y¢<‹î£\Ñþ&,tûR7ÿ\0¦ø2?\Ô\0=y6\îr×¸\ñ&\÷\Ì\É*B\ã1»þ\ÈM\Û\öcb\ßl\ÔÒ½å½¢Oi•\È\Õ$2Ç”1`‚udûŒpj“¨\Þ\É%\Ô\òMqpÀ³1$\÷\Øv\ÐV\ñ¬¡a(\Æ\0\Ò\Ù$¶2#?+¡J9”=I-uA\Ô ”¤7j#\"	\Z9£u”\äx\0\ç~y\àU€†TeÑŸ\öaˆ\Ãm’„j\÷\ÛÞ˜/ X\Ô\Ã*¡¶»2º #$ƒ¶w\î8\ÍV˜?_\Ç/\å\ÛHÁyXc\íYZµ\ØÛ‡¸\å?\ÇÔŠ Šc‡\Ã$.V2,`ä¶\çµ\Èb”\à’“¹(Í­252«)¡•£-™@ŒX\î22¢EM²7\ã\éŠM\ÙÃº#¢’“\ÂúXœ\ë^H\Ïo\ö\njc\'‰x¥‘Æ’{U„,\Û\ïYY[‹3ÌšEVŒjß‘\öªv-\ä¸eFƒ €\Ã\n\ÊÊ´‰–³K*\ÌY·A•\ÆÜ’*rN“\ö?Z\ÊÊ¼\Ö2X>xP\Ì¾3R\"+‚¼¦k++§IcN\n4@i8\Èy?	\Õ@\òÁ0Ž;y¦\\\îu\0ÃŸM«+*%§¾!­§1«H‘˜”¾[b\ò)c“\É\Ò3\ô­rŒ\å_2qÚ²²¨%Œ\Ùg•†_KÎ \çs\Í	,ä´€\Ç\É\È@>µ••2\'†.\á™\'Em¼ˆ@•‚\ê4—:ªM*(\ò«`VVP¸a\Ô\ö6#8Š\ÝØ‘(<hS\÷\Íee~°_\Ý!$‚}«tcŽÜŠ\ÊÊ…\îYº„†\Ï v­\Ül9¬¬¢$HQSƒYYEX3º–C\"EŸ!8\ï[Xª¼\È\Û\"²²•?±„|³‰<>ûŒl8©¥P6n{{VVQAš] kq’s@ú,3ùd}ý\élI#5±\'\ç\ñ€|”¹?ïŒ¬¥u\Óÿ\0¸Jy\r\Ïü™²–/\âDbX\ÎLg[\é>_\ÔoG´q<B]\n²k19Ak©\×\Î8\ÈÀ\"²²±þ£«\îÿ\Ù','2025-04-15 10:27:16',1,1);
/*!40000 ALTER TABLE `bilder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `eventID` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `startDate` datetime NOT NULL,
  `endDate` datetime NOT NULL,
  `userID` int(11) NOT NULL,
  `famID` int(11) NOT NULL,
  `carReserved` tinyint(1) DEFAULT 0,
  `category` varchar(50) NOT NULL DEFAULT 'Sonstiges',
  PRIMARY KEY (`eventID`),
  KEY `userID` (`userID`),
  KEY `famID` (`famID`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE,
  CONSTRAINT `events_ibfk_2` FOREIGN KEY (`famID`) REFERENCES `family` (`famID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,'Do-IT-PrÃ¤sentationen','2025-04-28 00:00:00','2025-04-29 00:00:00',1,1,1,'Arbeit'),(2,'Do-IT-PrÃ¤sentationen','2025-04-29 00:00:00','2025-04-30 00:00:00',1,1,0,'Arbeit'),(3,'Do-IT-PrÃ¤sentationen','2025-04-30 00:00:00','2025-05-01 00:00:00',1,1,0,'Arbeit'),(4,'Feiertag','2025-05-01 00:00:00','2025-05-02 00:00:00',1,1,1,'Freizeit'),(5,'TÃœV','2025-05-02 00:00:00','2025-05-03 00:00:00',1,1,1,'Sonstiges'),(6,'FamilienEssen','2025-04-27 00:00:00','2025-04-28 00:00:00',1,1,0,'Familie'),(7,'Feierabend','2025-04-30 15:30:00','2025-04-30 17:00:00',1,1,0,'Freizeit'),(8,'Beispiel Termin','2025-05-02 10:00:00','2025-05-02 13:00:00',1,1,1,'Sonstiges'),(9,'KurzTermin','2025-04-28 13:00:00','2025-04-28 13:30:00',1,1,0,'Familie');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `family`
--

DROP TABLE IF EXISTS `family`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `family` (
  `famID` int(11) NOT NULL AUTO_INCREMENT,
  `famName` varchar(255) NOT NULL,
  PRIMARY KEY (`famID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `family`
--

LOCK TABLES `family` WRITE;
/*!40000 ALTER TABLE `family` DISABLE KEYS */;
INSERT INTO `family` VALUES (1,'Musterfamilie');
/*!40000 ALTER TABLE `family` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invites`
--

DROP TABLE IF EXISTS `invites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invites` (
  `inviteID` int(11) NOT NULL AUTO_INCREMENT,
  `famID` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`inviteID`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `token` (`token`),
  KEY `famID` (`famID`),
  CONSTRAINT `invites_ibfk_1` FOREIGN KEY (`famID`) REFERENCES `family` (`famID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invites`
--

LOCK TABLES `invites` WRITE;
/*!40000 ALTER TABLE `invites` DISABLE KEYS */;
/*!40000 ALTER TABLE `invites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shop`
--

DROP TABLE IF EXISTS `shop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shop` (
  `shopID` int(11) NOT NULL AUTO_INCREMENT,
  `shopname` varchar(255) NOT NULL,
  PRIMARY KEY (`shopID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shop`
--

LOCK TABLES `shop` WRITE;
/*!40000 ALTER TABLE `shop` DISABLE KEYS */;
INSERT INTO `shop` VALUES (1,'Aldi'),(2,'Lidl'),(3,'Rewe');
/*!40000 ALTER TABLE `shop` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shopitems`
--

DROP TABLE IF EXISTS `shopitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shopitems` (
  `shopItemsID` int(11) NOT NULL AUTO_INCREMENT,
  `itemName` varchar(255) NOT NULL,
  `menge` int(11) NOT NULL,
  PRIMARY KEY (`shopItemsID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shopitems`
--

LOCK TABLES `shopitems` WRITE;
/*!40000 ALTER TABLE `shopitems` DISABLE KEYS */;
INSERT INTO `shopitems` VALUES (1,'Fahrrad',1),(2,'Apfel',3),(3,'Zucker',1);
/*!40000 ALTER TABLE `shopitems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `todo`
--

DROP TABLE IF EXISTS `todo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `todo` (
  `toDoID` int(11) NOT NULL AUTO_INCREMENT,
  `toDoName` varchar(255) NOT NULL,
  `ischecked` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`toDoID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `todo`
--

LOCK TABLES `todo` WRITE;
/*!40000 ALTER TABLE `todo` DISABLE KEYS */;
INSERT INTO `todo` VALUES (1,'ExampleToDo',0),(2,'ExampleToDo 2',0);
/*!40000 ALTER TABLE `todo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `userID` int(11) NOT NULL AUTO_INCREMENT,
  `vorname` varchar(255) NOT NULL,
  `nachname` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `famID` int(11) DEFAULT NULL,
  `profilbild` longblob DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` enum('m','w','other') DEFAULT NULL,
  PRIMARY KEY (`userID`),
  UNIQUE KEY `email` (`email`),
  KEY `famID` (`famID`),
  CONSTRAINT `user_ibfk_1` FOREIGN KEY (`famID`) REFERENCES `family` (`famID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'Dozent','Mustermann','dozent@example.com','$2y$10$xP6nj5V1Ied9QhikeTaxQO.RzHk9mWVUMM4BtoiflZztBnvSAc34u',1,NULL,NULL,NULL,NULL,NULL,NULL),(2,'Dozentfamilie','Musterfamilie','dozentfamily@example.com','$2y$10$zz0.QJovImSOOoBwmReL3.humj0eBtMkVL7/WrPmsJ1lYp0GWMEwW',1,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userapps`
--

DROP TABLE IF EXISTS `userapps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `userapps` (
  `userID` int(11) NOT NULL,
  `appID` int(11) NOT NULL,
  PRIMARY KEY (`userID`,`appID`),
  KEY `appID` (`appID`),
  CONSTRAINT `userapps_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE,
  CONSTRAINT `userapps_ibfk_2` FOREIGN KEY (`appID`) REFERENCES `app` (`appID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userapps`
--

LOCK TABLES `userapps` WRITE;
/*!40000 ALTER TABLE `userapps` DISABLE KEYS */;
INSERT INTO `userapps` VALUES (1,1),(1,2),(1,3),(1,4);
/*!40000 ALTER TABLE `userapps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `useritems`
--

DROP TABLE IF EXISTS `useritems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `useritems` (
  `userID` int(11) NOT NULL,
  `shopItemsID` int(11) NOT NULL,
  `shopID` int(11) NOT NULL,
  PRIMARY KEY (`userID`,`shopItemsID`),
  KEY `shopItemsID` (`shopItemsID`),
  KEY `shopID` (`shopID`),
  CONSTRAINT `useritems_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`),
  CONSTRAINT `useritems_ibfk_2` FOREIGN KEY (`shopItemsID`) REFERENCES `shopitems` (`shopItemsID`),
  CONSTRAINT `useritems_ibfk_3` FOREIGN KEY (`shopID`) REFERENCES `shop` (`shopID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `useritems`
--

LOCK TABLES `useritems` WRITE;
/*!40000 ALTER TABLE `useritems` DISABLE KEYS */;
INSERT INTO `useritems` VALUES (1,2,1),(1,3,2),(1,1,3);
/*!40000 ALTER TABLE `useritems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usertodo`
--

DROP TABLE IF EXISTS `usertodo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usertodo` (
  `userID` int(11) NOT NULL,
  `toDoID` int(11) NOT NULL,
  PRIMARY KEY (`userID`,`toDoID`),
  KEY `toDoID` (`toDoID`),
  CONSTRAINT `usertodo_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`),
  CONSTRAINT `usertodo_ibfk_2` FOREIGN KEY (`toDoID`) REFERENCES `todo` (`toDoID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usertodo`
--

LOCK TABLES `usertodo` WRITE;
/*!40000 ALTER TABLE `usertodo` DISABLE KEYS */;
INSERT INTO `usertodo` VALUES (1,1),(1,2);
/*!40000 ALTER TABLE `usertodo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'familyboard'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-15 12:39:43
