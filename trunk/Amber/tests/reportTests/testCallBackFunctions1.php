<?php
////////////////////////////////////////////////////////////////////////
//
// Test classes are user call back functions, extended by
//      - getLayout() which produces the layout of the form
//      - assert which does the tests
//      - getData which returns the recordset
//
////////////////////////////////////////////////////////////////////////


// This test checks, if all userfunctions are called correctly in a report with data

class testCallBackFunctions1 extends AmberReport_UserFunctions
// a simple report in the docu
{
  function Report_Open(&$Cancel)
  { 
    $name = 'Report_Open';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
  }
  
  function Report_ComputeColumns(&$Cancel, &$col)
  {
    $name = 'Report_ComputeColumns';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
  }
  
  function Report_OnLoadData(&$Cancel) 
  {
    $name = 'Report_OnLoadData';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
  }
  
  function Report_NoData(&$Cancel)
  {
    $name = 'Report_NoData';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
  }

  function Report_EvaluateExpressions()
  {
    $this->calls['Report_EvaluateExpressions'] ++;
  }

  function Report_OnNextRecord()
  {
    $this->calls['Report_OnNextRecord'] ++;
  }

  function Report_Page()
  {
    $this->calls['Report_Page'] ++;
  }

  function Report_Close()
  {
    $this->calls['Report_Close'] ++;
  }

  function Detailbereich_Format(&$Cancel, $FormatCount)
  { 
    $name = 'Detailbereich_Format';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
    $this->test->assertEquals(1, $FormatCount, get_class($this) . '->' . $name);
  }

  function Detailbereich_Print(&$Cancel, $PrintCount)
  {
    $name = 'Detailbereich_Print';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
    $this->test->assertEquals(1, $PrintCount, get_class($this) . '->' . $name);
  }

  function Berichtskopf_Format(&$Cancel, $FormatCount)
  {
    $name = 'Berichtskopf_Format';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
    $this->test->assertEquals(1, $FormatCount, get_class($this) . '->' . $name);
  }

  function Berichtskopf_Print(&$Cancel, $PrintCount)
  {
    $name = 'Berichtskopf_Print';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
    $this->test->assertEquals(1, $PrintCount, get_class($this) . '->' . $name);
  }

  function Berichtsfuss_Format(&$Cancel, $FormatCount)
  {
    $name = 'Berichtsfuss_Format';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
    $this->test->assertEquals(1, $FormatCount, get_class($this) . '->' . $name);
  }

  function Berichtsfuss_Print(&$Cancel, $PrintCount)
  {
    $name = 'Berichtsfuss_Print';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
    $this->test->assertEquals(1, $PrintCount, get_class($this) . '->' . $name);
  }

  function Seitenkopfbereich_Format(&$Cancel, $FormatCount)
  {
    $name = 'Seitenkopfbereich_Format';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
    $this->test->assertEquals(1, $FormatCount, get_class($this) . '->' . $name);
  }

  function Seitenkopfbereich_Print(&$Cancel, $PrintCount)
  {
    $name = 'Seitenkopfbereich_Print';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
    $this->test->assertEquals(1, $PrintCount, get_class($this) . '->' . $name);
  }

  function Seitenfussbereich_Format(&$Cancel, $FormatCount)
  {
    $name = 'Seitenfussbereich_Format';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
    $this->test->assertEquals(1, $FormatCount, get_class($this) . '->' . $name);
  }

  function Seitenfussbereich_Print(&$Cancel, $PrintCount)
  {
    $name = 'Seitenfussbereich_Print';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
    $this->test->assertEquals(1, $PrintCount, get_class($this) . '->' . $name);
  }

  function Gruppenkopf0_Format(&$Cancel, $FormatCount)
  {
    $name = 'Gruppenkopf0_Format';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
    $this->test->assertEquals(1, $FormatCount, get_class($this) . '->' . $name);
  }

  function Gruppenkopf0_Print(&$Cancel, $PrintCount)
  {
    $name = 'Gruppenkopf0_Print';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
    $this->test->assertEquals(1, $PrintCount, get_class($this) . '->' . $name);
  }

  function Gruppenfuss1_Format(&$Cancel, $FormatCount)
  {
    $name = 'Gruppenfuss1_Format';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
    $this->test->assertEquals(1, $FormatCount, get_class($this) . '->' . $name);
  }

  function Gruppenfuss1_Print(&$Cancel, $PrintCount)
  {
    $name = 'Gruppenfuss1_Print';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
    $this->test->assertEquals(1, $PrintCount, get_class($this) . '->' . $name);
  }

  function Gruppenkopf2_Format(&$Cancel, $FormatCount)
  {
    $name = 'Gruppenkopf2_Format';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
    $this->test->assertEquals(1, $FormatCount, get_class($this) . '->' . $name);
  }

  function Gruppenkopf2_Print(&$Cancel, $PrintCount)
  {
    $name = 'Gruppenkopf2_Print';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
    $this->test->assertEquals(1, $PrintCount, get_class($this) . '->' . $name);
  }

  function Gruppenfuss3_Format(&$Cancel, $FormatCount)
  {
    $name = 'Gruppenfuss3_Format';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->'.$name);
    $this->test->assertEquals(1, $FormatCount, get_class($this) . '->' . $name);
  }

  function Gruppenfuss3_Print(&$Cancel, $PrintCount)
  {
    $name = 'Gruppenfuss3_Print';
    $this->calls[$name] ++;
    $this->test->assertFalse($Cancel, get_class($this) . '->' . $name);
    $this->test->assertEquals(1, $PrintCount, get_class($this) . '->' . $name);
  }
  
//TEST  
  function assertHtml($html)
  {
    $test =& $this->test;
    $id = get_class($this) . '->assertHtml'; 
    $test->assertEquals(1, $this->calls['Report_Open'], $id . ' Report_Open');
    $test->assertEquals(8, $this->calls['Report_ComputeColumns'], $id . ' Report_ComputeColumns');
    $test->assertEquals(8, $this->calls['Report_OnLoadData'], $id . ' Report_OnLoadData');
    $test->assertEquals(0, $this->calls['Report_NoData'], $id . ' Report_NoData');
    $test->assertEquals(8, $this->calls['Report_EvaluateExpressions'], $id . ' Report_EvaluateExpressions');
    $test->assertEquals(8, $this->calls['Report_OnNextRecord'], $id . ' Report_OnNextRecord');
    $test->assertEquals(1, $this->calls['Report_Page'], $id . ' Report_Page');
    $test->assertEquals(1, $this->calls['Report_Close'], $id . ' Report_Close');
    $test->assertEquals(8, $this->calls['Detailbereich_Format'], $id . ' Detailbereich_Format');
    $test->assertEquals(8, $this->calls['Detailbereich_Print'], $id . ' Detailbereich_Print');
    $test->assertEquals(1, $this->calls['Berichtskopf_Format'], $id . ' Berichtskopf_Format');
    $test->assertEquals(1, $this->calls['Berichtskopf_Print'], $id . ' Berichtskopf_Print');
    $test->assertEquals(1, $this->calls['Berichtsfuss_Format'], $id . ' Berichtsfuss_Format');
    $test->assertEquals(1, $this->calls['Berichtsfuss_Print'], $id . ' Berichtsfuss_Print');
    $test->assertEquals(1, $this->calls['Seitenkopfbereich_Format'], $id . ' Seitenkopfbereich_Format');
    $test->assertEquals(1, $this->calls['Seitenfussbereich_Print'], $id . ' Seitenfussbereich_Print');
    $test->assertEquals(4, $this->calls['Gruppenkopf0_Format'], $id . ' Gruppenkopf0_Format');
    $test->assertEquals(4, $this->calls['Gruppenkopf0_Print'], $id . ' Gruppenkopf0_Print');
    $test->assertEquals(4, $this->calls['Gruppenfuss1_Format'], $id . ' Gruppenfuss1_Format');
    $test->assertEquals(4, $this->calls['Gruppenfuss1_Print'], $id . ' Gruppenfuss1_Print');
    $test->assertEquals(6, $this->calls['Gruppenkopf2_Format'], $id . ' Gruppenkopf2_Format');
    $test->assertEquals(6, $this->calls['Gruppenkopf2_Print'], $id . ' Gruppenkopf2_Print');
    $test->assertEquals(6, $this->calls['Gruppenfuss3_Format'], $id . ' Gruppenfuss3_Format');
    $test->assertEquals(6, $this->calls['Gruppenfuss3_Print'], $id . ' Gruppenfuss3_Print');
  }
  
  function assertPdf($html)
  {
  }
  
  function getLayout()
  { $s = <<<EOD
<?xml version="1.0" encoding="ISO-8859-1"?>
<report>
    <RecordSource>SELECT customer.lastname, customer.firstname FROM customer;</RecordSource>
    <BorderStyle>2</BorderStyle>
    <Width>7200</Width>
    <Picture>(keines)</Picture>
    <PicturePages>0</PicturePages>
    <LogicalPageWidth>9015</LogicalPageWidth>
    <Name>testCallbackFunctions</Name>
    <OnOpen>[Event Procedure]</OnOpen>
    <OnClose>[Event Procedure]</OnClose>
    <PictureData></PictureData>
    <PicturePalette></PicturePalette>
    <HasModule>-1</HasModule>
    <Orientation>0</Orientation>
    <Printer>
        <BottomMargin>1441</BottomMargin>
        <ColorMode>2</ColorMode>
        <ColumnSpacing>360</ColumnSpacing>
        <Copies>1</Copies>
        <DataOnly>0</DataOnly>
        <DefaultSize>1</DefaultSize>
        <ItemLayout>1953</ItemLayout>
        <ItemsAcross>1</ItemsAcross>
        <ItemSizeHeight>300</ItemSizeHeight>
        <ItemSizeWidth>7200</ItemSizeWidth>
        <LeftMargin>1448</LeftMargin>
        <Orientation>1</Orientation>
        <PaperSize>9</PaperSize>
        <RightMargin>1441</RightMargin>
        <RowSpacing>0</RowSpacing>
        <TopMargin>1441</TopMargin>
    </Printer>
    <GroupLevels> 
        <item id = "0">
            <index>0</index>
            <ControlSource>lastname</ControlSource>
            <SortOrder>0</SortOrder>
            <GroupHeader>-1</GroupHeader>
            <GroupFooter>-1</GroupFooter>
            <GroupOn>0</GroupOn>
            <GroupInterval>1</GroupInterval>
        </item>
        <item id = "1">
            <index>1</index>
            <ControlSource>firstname</ControlSource>
            <SortOrder>0</SortOrder>
            <GroupHeader>-1</GroupHeader>
            <GroupFooter>-1</GroupFooter>
            <GroupOn>0</GroupOn>
            <GroupInterval>1</GroupInterval>
        </item>
    </GroupLevels>
    <ReportHeader> 
        <EventProcPrefix>Berichtskopf</EventProcPrefix>
        <Name>Berichtskopf</Name>
        <KeepTogether>-1</KeepTogether>
        <Height>360</Height>
        <OnFormat>[Event Procedure]</OnFormat>
        <OnPrint>[Event Procedure]</OnPrint>
        <OnRetreat>[Event Procedure]</OnRetreat>
        <Controls>
            <item id="Bezeichnungsfeld0">
                <EventProcPrefix>Bezeichnungsfeld0</EventProcPrefix>
                <Name>Bezeichnungsfeld0</Name>
                <ControlType>100</ControlType>
                <Caption>ReportHeader</Caption>
                <Left>0</Left>
                <Top>0</Top>
                <Width>7200</Width>
                <Height>360</Height>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>12</FontSize>
                <FontWeight>700</FontWeight>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>0</TextAlign>
                <FontBold>1</FontBold>
                <Section>1</Section>
                <zIndex>10</zIndex>
            </item>
        </Controls>
    </ReportHeader>
    <PageHeader> 
        <EventProcPrefix>Seitenkopfbereich</EventProcPrefix>
        <Name>Seitenkopfbereich</Name>
        <Height>360</Height>
        <OnFormat>[Event Procedure]</OnFormat>
        <OnPrint>[Event Procedure]</OnPrint>
        <Controls>
            <item id="Bezeichnungsfeld1">
                <EventProcPrefix>Bezeichnungsfeld1</EventProcPrefix>
                <Name>Bezeichnungsfeld1</Name>
                <ControlType>100</ControlType>
                <Caption>PageHeader</Caption>
                <Left>0</Left>
                <Top>0</Top>
                <Width>7200</Width>
                <Height>360</Height>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>12</FontSize>
                <FontWeight>700</FontWeight>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>0</TextAlign>
                <FontBold>1</FontBold>
                <Section>3</Section>
                <zIndex>10</zIndex>
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
        <Height>360</Height>
        <OnFormat>[Event Procedure]</OnFormat>
        <OnPrint>[Event Procedure]</OnPrint>
        <OnRetreat>[Event Procedure]</OnRetreat>
        <Controls>
            <item id="Bezeichnungsfeld2">
                <EventProcPrefix>Bezeichnungsfeld2</EventProcPrefix>
                <Name>Bezeichnungsfeld2</Name>
                <ControlType>100</ControlType>
                <Caption>GroupHeader1</Caption>
                <Left>0</Left>
                <Top>0</Top>
                <Width>7200</Width>
                <Height>360</Height>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>12</FontSize>
                <FontWeight>700</FontWeight>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>0</TextAlign>
                <FontBold>1</FontBold>
                <Section>5</Section>
                <zIndex>10</zIndex>
            </item>
        </Controls>
        </item>
        <item id="1"> 
            <index>1</index>
        <EventProcPrefix>Gruppenkopf2</EventProcPrefix>
        <Name>Gruppenkopf2</Name>
        <KeepTogether>-1</KeepTogether>
        <RepeatSection>0</RepeatSection>
        <Height>360</Height>
        <OnFormat>[Event Procedure]</OnFormat>
        <OnPrint>[Event Procedure]</OnPrint>
        <OnRetreat>[Event Procedure]</OnRetreat>
        <Controls>
            <item id="Bezeichnungsfeld3">
                <EventProcPrefix>Bezeichnungsfeld3</EventProcPrefix>
                <Name>Bezeichnungsfeld3</Name>
                <ControlType>100</ControlType>
                <Caption>GroupHeader2</Caption>
                <Left>0</Left>
                <Top>0</Top>
                <Width>7200</Width>
                <Height>360</Height>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>12</FontSize>
                <FontWeight>700</FontWeight>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>0</TextAlign>
                <FontBold>1</FontBold>
                <Section>7</Section>
                <zIndex>10</zIndex>
            </item>
        </Controls>
        </item>
    </GroupHeaders>
    <Detail> 
        <EventProcPrefix>Detailbereich</EventProcPrefix>
        <Name>Detailbereich</Name>
        <KeepTogether>-1</KeepTogether>
        <Height>360</Height>
        <OnFormat>[Event Procedure]</OnFormat>
        <OnPrint>[Event Procedure]</OnPrint>
        <OnRetreat>[Event Procedure]</OnRetreat>
        <Controls>
            <item id="Bezeichnungsfeld4">
                <EventProcPrefix>Bezeichnungsfeld4</EventProcPrefix>
                <Name>Bezeichnungsfeld4</Name>
                <ControlType>100</ControlType>
                <Caption>Detail</Caption>
                <Left>0</Left>
                <Top>0</Top>
                <Width>7200</Width>
                <Height>360</Height>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>12</FontSize>
                <FontWeight>700</FontWeight>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>0</TextAlign>
                <FontBold>1</FontBold>
                <Section>0</Section>
                <zIndex>10</zIndex>
            </item>
        </Controls>
    </Detail>
    <GroupFooters>
        <item id="1"> 
            <index>1</index>
        <EventProcPrefix>Gruppenfuss3</EventProcPrefix>
        <Name>Gruppenfuﬂ3</Name>
        <KeepTogether>-1</KeepTogether>
        <Height>360</Height>
        <OnFormat>[Event Procedure]</OnFormat>
        <OnPrint>[Event Procedure]</OnPrint>
        <OnRetreat>[Event Procedure]</OnRetreat>
        <Controls>
            <item id="Bezeichnungsfeld5">
                <EventProcPrefix>Bezeichnungsfeld5</EventProcPrefix>
                <Name>Bezeichnungsfeld5</Name>
                <ControlType>100</ControlType>
                <Caption>GroupFooter2</Caption>
                <Left>0</Left>
                <Top>0</Top>
                <Width>7200</Width>
                <Height>360</Height>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>12</FontSize>
                <FontWeight>700</FontWeight>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>0</TextAlign>
                <FontBold>1</FontBold>
                <Section>8</Section>
                <zIndex>10</zIndex>
            </item>
        </Controls>
        </item>
        <item id="0"> 
            <index>0</index>
        <EventProcPrefix>Gruppenfuss1</EventProcPrefix>
        <Name>Gruppenfuﬂ1</Name>
        <KeepTogether>-1</KeepTogether>
        <Height>360</Height>
        <OnFormat>[Event Procedure]</OnFormat>
        <OnPrint>[Event Procedure]</OnPrint>
        <OnRetreat>[Event Procedure]</OnRetreat>
        <Controls>
            <item id="Bezeichnungsfeld6">
                <EventProcPrefix>Bezeichnungsfeld6</EventProcPrefix>
                <Name>Bezeichnungsfeld6</Name>
                <ControlType>100</ControlType>
                <Caption>GroupFooter2</Caption>
                <Left>0</Left>
                <Top>0</Top>
                <Width>7200</Width>
                <Height>360</Height>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>12</FontSize>
                <FontWeight>700</FontWeight>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>0</TextAlign>
                <FontBold>1</FontBold>
                <Section>6</Section>
                <zIndex>10</zIndex>
            </item>
        </Controls>
        </item>
    </GroupFooters>
    <PageFooter> 
        <EventProcPrefix>Seitenfussbereich</EventProcPrefix>
        <Name>Seitenfuﬂbereich</Name>
        <Height>360</Height>
        <OnFormat>[Event Procedure]</OnFormat>
        <OnPrint>[Event Procedure]</OnPrint>
        <Controls>
            <item id="Bezeichnungsfeld7">
                <EventProcPrefix>Bezeichnungsfeld7</EventProcPrefix>
                <Name>Bezeichnungsfeld7</Name>
                <ControlType>100</ControlType>
                <Caption>PageFooter</Caption>
                <Left>0</Left>
                <Top>0</Top>
                <Width>7200</Width>
                <Height>360</Height>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>12</FontSize>
                <FontWeight>700</FontWeight>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>0</TextAlign>
                <FontBold>1</FontBold>
                <Section>4</Section>
                <zIndex>10</zIndex>
            </item>
        </Controls>
    </PageFooter>
    <ReportFooter> 
        <EventProcPrefix>Berichtsfuss</EventProcPrefix>
        <Name>Berichtsfuﬂ</Name>
        <KeepTogether>-1</KeepTogether>
        <Height>360</Height>
        <OnPrint>[Event Procedure]</OnPrint>
        <OnRetreat>[Event Procedure]</OnRetreat>
        <Controls>
            <item id="Bezeichnungsfeld8">
                <EventProcPrefix>Bezeichnungsfeld8</EventProcPrefix>
                <Name>Bezeichnungsfeld8</Name>
                <ControlType>100</ControlType>
                <Caption>ReportFooter</Caption>
                <Left>0</Left>
                <Top>0</Top>
                <Width>7200</Width>
                <Height>360</Height>
                <BorderStyle>0</BorderStyle>
                <FontName>Arial</FontName>
                <FontSize>12</FontSize>
                <FontWeight>700</FontWeight>
                <TextFontCharSet>0</TextFontCharSet>
                <TextAlign>0</TextAlign>
                <FontBold>1</FontBold>
                <Section>2</Section>
                <zIndex>10</zIndex>
            </item>
        </Controls>
    </ReportFooter>
</report>
EOD;
    return $s;
  }
  


  function getData()
  {
    $res = array(
      array('lastname' => 'Jackson', 'firstname' => 'John'),
      array('lastname' => 'Jackson', 'firstname' => 'John'),
      array('lastname' => 'Jackson', 'firstname' => 'Alice'),
      array('lastname' => 'Jackson', 'firstname' => 'Alice'),
      array('lastname' => 'Tompson', 'firstname' => 'Terry'),
      array('lastname' => 'Tompson', 'firstname' => 'Ben'),
      array('lastname' => 'Smith', 'firstname' => 'Sam'),
      array('lastname' => 'Clark', 'firstname' => 'Catherine'),
    );
    return $res;
  }
}     
?>