<?php
////////////////////////////////////////////////////////////////////////
//
// Test classes are user call back functions, extended by
//      - getLayout() which produces the layout of the form
//      - assert which does the tests
//      - getData which returns the recordset
//
////////////////////////////////////////////////////////////////////////


class exampleInvoiceList extends AmberReport_UserFunctions
// a simple report in the docu
{
 
  function Report_EvaluateExpressions()
  {
    $val =& $this->val;
    $col =& $this->col;

    $val['name'] = $col['firstname'] . ' ' . $col['lastname'];
    $this->Text18->addValue($val['amount'], 'sum');        
    $val['Text26'] = 'Page  ' . $this->Page();

  }
                       
  
//TEST  
  function assertHtml($html)
  {
    $test =& $this->test;
    $id = get_class($this) . '->assertHtml'; 
    $test->assertContains('>Sample Company Ltd.<', $html, $id . ' Title');
    $test->assertContains('>Invoices<',            $html, $id . ' SubTitle');
    $test->assertContains('>Alice Anderson<',      $html, $id . ' Alice');
    $test->assertContains('>Susan Smith<',         $html, $id . ' Susan');
    $test->assertContains('>Page  1<',             $html, $id . ' Page');
  }
  
  function assertPdf($html)
  {
    $test =& $this->test;
    $id = get_class($this) . '->assertPdf'; 
    $test->assertContains('Sample Company Ltd.', $html, $id . ' Title');
    $test->assertContains('Invoices',            $html, $id . ' SubTitle');
    $test->assertContains('Alice Anderson',      $html, $id . ' Alice');
    $test->assertContains('Susan Smith',         $html, $id . ' Susan');
    $test->assertContains('Page  1',             $html, $id . ' Page');
  }
  function getLayout()
  { $s = <<<EOD
<?xml version="1.0" encoding="ISO-8859-1"?>
<report>
    <RecordSource>SELECT customer.*, bill.* FROM customer INNER JOIN bill ON [customer].[id]=[bill].[customer]; </RecordSource>
    <Width>8844</Width>
    <Picture>(keines)</Picture>
    <PicturePages>0</PicturePages>
    <LogicalPageWidth>9070</LogicalPageWidth>
    <Name>Invoice list</Name>
    <PictureData></PictureData>
    <PicturePalette></PicturePalette>
    <Printer>
        <BottomMargin>1417</BottomMargin>
        <ColorMode>1</ColorMode>
        <ColumnSpacing>360</ColumnSpacing>
        <Copies>1</Copies>
        <DataOnly>0</DataOnly>
        <DefaultSize>1</DefaultSize>
        <ItemLayout>1953</ItemLayout>
        <ItemsAcross>1</ItemsAcross>
        <ItemSizeHeight>270</ItemSizeHeight>
        <ItemSizeWidth>8844</ItemSizeWidth>
        <LeftMargin>1417</LeftMargin>
        <Orientation>1</Orientation>
        <PaperSize>9</PaperSize>
        <RightMargin>1417</RightMargin>
        <RowSpacing>0</RowSpacing>
        <TopMargin>1417</TopMargin>
    </Printer>
    <GroupLevels> 
        <item id = "0">
            <index>0</index>
            <ControlSource>year</ControlSource>
            <SortOrder>0</SortOrder>
            <GroupHeader>-1</GroupHeader>
            <GroupFooter>-1</GroupFooter>
            <GroupOn>0</GroupOn>
            <GroupInterval>1</GroupInterval>
            <KeepTogether>1</KeepTogether>
        </item>
        <item id = "1">
            <index>1</index>
            <ControlSource>lastname</ControlSource>
            <SortOrder>0</SortOrder>
            <GroupHeader>0</GroupHeader>
            <GroupFooter>0</GroupFooter>
            <GroupOn>0</GroupOn>
            <GroupInterval>1</GroupInterval>
        </item>
    </GroupLevels>
    <PageHeader> 
        <EventProcPrefix>Seitenkopfbereich</EventProcPrefix>
        <Name>Seitenkopfbereich</Name>
        <Height>2381</Height>
        <Controls>
            <item id="Bezeichnungsfeld9">
                <EventProcPrefix>Bezeichnungsfeld9</EventProcPrefix>
                <Name>Bezeichnungsfeld9</Name>
                <ControlType>100</ControlType>
                <Caption>Sample Company Ltd.</Caption>
                <Left>0</Left>
                <Top>283</Top>
                <Width>8835</Width>
                <Height>615</Height>
                <BackStyle>1</BackStyle>
                <BackColor>6697881</BackColor>
                <BorderStyle>1</BorderStyle>
                <ForeColor>16777215</ForeColor>
                <FontName>Arial</FontName>
                <FontSize>24</FontSize>
                <FontWeight>700</FontWeight>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>2</TextAlign>
                <FontBold>1</FontBold>
                <Section>3</Section>
                <zIndex>10</zIndex>
            </item>
            <item id="Bezeichnungsfeld10">
                <EventProcPrefix>Bezeichnungsfeld10</EventProcPrefix>
                <Name>Bezeichnungsfeld10</Name>
                <ControlType>100</ControlType>
                <Caption>Invoices</Caption>
                <Left>3798</Left>
                <Top>1417</Top>
                <Width>1200</Width>
                <Height>375</Height>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>14</FontSize>
                <FontWeight>700</FontWeight>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>0</TextAlign>
                <FontBold>1</FontBold>
                <Section>3</Section>
                <zIndex>20</zIndex>
            </item>
        </Controls>
    </PageHeader>
    <GroupHeaders>
        <item id="0"> 
            <index>0</index>
        <EventProcPrefix>Gruppenkopf0</EventProcPrefix>
        <Name>Gruppenkopf0</Name>
        <KeepTogether>-1</KeepTogether>
        <RepeatSection>0</RepeatSection>
        <Height>793</Height>
        <Controls>
            <item id="year">
                <EventProcPrefix>year</EventProcPrefix>
                <Name>year</Name>
                <ControlType>109</ControlType>
                <ControlSource>year</ControlSource>
                <Left>0</Left>
                <Top>0</Top>
                <Width>8841</Width>
                <Height>330</Height>
                <BackStyle>1</BackStyle>
                <BackColor>10053222</BackColor>
                <BorderStyle>0</BorderStyle>
                <ForeColor>16777215</ForeColor>
                <FontName>Arial</FontName>
                <FontSize>12</FontSize>
                <FontWeight>700</FontWeight>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>2</TextAlign>
                <FontBold>1</FontBold>
                <Section>5</Section>
                <zIndex>10</zIndex>
            </item>
            <item id="Bezeichnungsfeld21">
                <EventProcPrefix>Bezeichnungsfeld21</EventProcPrefix>
                <Name>Bezeichnungsfeld21</Name>
                <ControlType>100</ControlType>
                <Caption>Title</Caption>
                <Left>283</Left>
                <Top>453</Top>
                <Width>375</Width>
                <Height>225</Height>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>8</FontSize>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>0</TextAlign>
                <Section>5</Section>
                <zIndex>20</zIndex>
            </item>
            <item id="Bezeichnungsfeld22">
                <EventProcPrefix>Bezeichnungsfeld22</EventProcPrefix>
                <Name>Bezeichnungsfeld22</Name>
                <ControlType>100</ControlType>
                <Caption>Name</Caption>
                <Left>1020</Left>
                <Top>453</Top>
                <Width>510</Width>
                <Height>225</Height>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>8</FontSize>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>0</TextAlign>
                <Section>5</Section>
                <zIndex>30</zIndex>
            </item>
            <item id="Bezeichnungsfeld23">
                <EventProcPrefix>Bezeichnungsfeld23</EventProcPrefix>
                <Name>Bezeichnungsfeld23</Name>
                <ControlType>100</ControlType>
                <Caption>Billing date</Caption>
                <Left>4025</Left>
                <Top>453</Top>
                <Width>1710</Width>
                <Height>225</Height>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>8</FontSize>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>3</TextAlign>
                <Section>5</Section>
                <zIndex>40</zIndex>
            </item>
            <item id="Bezeichnungsfeld24">
                <EventProcPrefix>Bezeichnungsfeld24</EventProcPrefix>
                <Name>Bezeichnungsfeld24</Name>
                <ControlType>100</ControlType>
                <Caption>Amount</Caption>
                <Left>6349</Left>
                <Top>453</Top>
                <Width>1710</Width>
                <Height>225</Height>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>8</FontSize>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>3</TextAlign>
                <Section>5</Section>
                <zIndex>50</zIndex>
            </item>
            <item id="Rechteck25">
                <EventProcPrefix>Rechteck25</EventProcPrefix>
                <Name>Rechteck25</Name>
                <ControlType>101</ControlType>
                <Left>0</Left>
                <Top>737</Top>
                <Width>8613</Width>
                <Height>0</Height>
                <BackStyle>1</BackStyle>
                <BorderStyle>1</BorderStyle>
                <OldBorderStyle>1</OldBorderStyle>
                <Section>5</Section>
                <zIndex>60</zIndex>
            </item>
        </Controls>
        </item>
    </GroupHeaders>
    <Detail> 
        <EventProcPrefix>Detailbereich</EventProcPrefix>
        <Name>Detailbereich</Name>
        <KeepTogether>-1</KeepTogether>
        <Height>570</Height>
        <Controls>
            <item id="name">
                <EventProcPrefix>name</EventProcPrefix>
                <Name>name</Name>
                <ControlType>109</ControlType>
                <ControlSource>=[firstname] &amp; &quot; &quot; &amp; [lastname]</ControlSource>
                <Left>1022</Left>
                <Top>0</Top>
                <Width>2766</Width>
                <Height>270</Height>
                <BackStyle>1</BackStyle>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>10</FontSize>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>0</TextAlign>
                <Section>0</Section>
                <zIndex>10</zIndex>
            </item>
            <item id="title">
                <EventProcPrefix>title</EventProcPrefix>
                <Name>title</Name>
                <ControlType>109</ControlType>
                <ControlSource>title</ControlSource>
                <Left>283</Left>
                <Top>0</Top>
                <Width>696</Width>
                <Height>270</Height>
                <BackStyle>1</BackStyle>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>10</FontSize>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>0</TextAlign>
                <Section>0</Section>
                <zIndex>20</zIndex>
            </item>
            <item id="date">
                <EventProcPrefix>date</EventProcPrefix>
                <Name>date</Name>
                <ControlType>109</ControlType>
                <ControlSource>date</ControlSource>
                <Left>4025</Left>
                <Top>0</Top>
                <Width>1701</Width>
                <Height>270</Height>
                <BackStyle>1</BackStyle>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>10</FontSize>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>3</TextAlign>
                <Section>0</Section>
                <zIndex>30</zIndex>
            </item>
            <item id="amount">
                <EventProcPrefix>amount</EventProcPrefix>
                <Name>amount</Name>
                <ControlType>109</ControlType>
                <ControlSource>amount</ControlSource>
                <Format>Euro</Format>
                <Left>6349</Left>
                <Top>0</Top>
                <Width>1701</Width>
                <Height>270</Height>
                <BackStyle>1</BackStyle>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>10</FontSize>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>3</TextAlign>
                <Section>0</Section>
                <zIndex>40</zIndex>
            </item>
            <item id="amountRunning">
                <EventProcPrefix>amountRunning</EventProcPrefix>
                <Name>amountRunning</Name>
                <ControlType>109</ControlType>
                <ControlSource>amount</ControlSource>
                <Format>Euro</Format>
                <Visible>1</Visible>
                <RunningSum>1</RunningSum>
                <Left>8077</Left>
                <Top>0</Top>
                <Width>716</Width>
                <Height>270</Height>
                <BackStyle>1</BackStyle>
                <BackColor>255</BackColor>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>8</FontSize>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>3</TextAlign>
                <Section>0</Section>
                <zIndex>50</zIndex>
            </item>
        </Controls>
    </Detail>
    <GroupFooters>
        <item id="0"> 
        <index>0</index>
        <EventProcPrefix>Gruppenfuﬂ0</EventProcPrefix>
        <Name>Gruppenfuﬂ0</Name>
        <KeepTogether>-1</KeepTogether>
        <Height>1133</Height>
        <Controls>
            <item id="Bezeichnungsfeld19">
                <EventProcPrefix>Bezeichnungsfeld19</EventProcPrefix>
                <Name>Bezeichnungsfeld19</Name>
                <ControlType>100</ControlType>
                <Caption>Total:</Caption>
                <Left>283</Left>
                <Top>113</Top>
                <Width>615</Width>
                <Height>285</Height>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>10</FontSize>
                <FontWeight>700</FontWeight>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>0</TextAlign>
                <FontBold>1</FontBold>
                <Section>6</Section>
                <zIndex>20</zIndex>
            </item>
            <item id="Rechteck20">
                <EventProcPrefix>Rechteck20</EventProcPrefix>
                <Name>Rechteck20</Name>
                <ControlType>101</ControlType>
                <Left>61</Left>
                <Top>56</Top>
                <Width>8613</Width>
                <Height>0</Height>
                <BackStyle>1</BackStyle>
                <BorderStyle>1</BorderStyle>
                <OldBorderStyle>1</OldBorderStyle>
                <Section>6</Section>
                <zIndex>30</zIndex>
            </item>
            <item id="Text18">
                <EventProcPrefix>Text18</EventProcPrefix>
                <Name>Text18</Name>
                <ControlType>109</ControlType>
                <ControlSource>=[amountRunning]</ControlSource>
                <Format>Euro</Format>
                <Left>6029</Left>
                <Top>213</Top>
                <Width>2011</Width>
                <Height>270</Height>
                <BackStyle>1</BackStyle>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>10</FontSize>
                <FontWeight>700</FontWeight>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>3</TextAlign>
                <FontBold>1</FontBold>
                <Section>6</Section>
                <zIndex>10</zIndex>
            </item>
        </Controls>
        </item>
    </GroupFooters>
    <PageFooter> 
        <EventProcPrefix>Seitenfuﬂbereich</EventProcPrefix>
        <Name>Seitenfuﬂbereich</Name>
        <Height>510</Height>
        <Controls>
            <item id="Text26">
                <EventProcPrefix>Text26</EventProcPrefix>
                <Name>Text26</Name>
                <ControlType>109</ControlType>
                <ControlSource>=&quot;Page  &quot; &amp; [Page]</ControlSource>
                <Left>4</Left>
                <Top>113</Top>
                <Width>8781</Width>
                <Height>240</Height>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>8</FontSize>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>2</TextAlign>
                <Section>4</Section>
                <zIndex>10</zIndex>
            </item>
        </Controls>
    </PageFooter>
</report>
EOD;
    return $s;
  }
  
  function getData()
  {  
    $customer = array(
      array('id'=>1, 'title'=>'Mr.', 'lastname'=>'Jackson', 'firstname'=>'John'),  	   	   	
      array('id'=>2, 'title'=>'Mr.', 'lastname'=>'Bown', 'firstname'=>'Bob'), 	  	  	
      array('id'=>3, 'title'=>'Mrs.', 'lastname'=>'Anderson', 'firstname'=>'Alice'), 	  	  	
      array('id'=>4, 'title'=>'Ms.', 'lastname'=>'Smith', 'firstname'=>'Susan'), 	  	  	
      array('id'=>5, 'title'=>'Mr.', 'lastname'=>'Tompson', 'firstname'=>'Terry'), 	  	  	
      array('id'=>6, 'title'=>'Mr.', 'lastname'=>'Bean', 'firstname'=>'Ben'), 	  	  	
      array('id'=>7, 'title'=>'Mr.', 'lastname'=>'Smith', 'firstname'=>'Sam'), 	  	  	
      array('id'=>8, 'title'=>'Mrs.', 'lastname'=>'Clark', 'firstname'=>'Catherine')	  	  	
    ); 
    return $customer; 
  }
}     
?>