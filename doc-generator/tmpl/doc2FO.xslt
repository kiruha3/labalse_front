<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:ext="http://exslt.org/common" version="1.0">
	<xsl:output method="xml" encoding="UTF-8" indent="yes"/>
	
	<xsl:variable name="lowercase" select="'abcdefghijklmnopqrstuvwxyzабвгдеёжзийклмнопрстуфхцчшщъыьэюя'" />
	<xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ'" />

	<xsl:variable name="docDataConstantsSrc">
		<constants-data>
			<paper-formats>
				<format name="A3p" width="297mm" height="420mm"/>
				<format name="A3l" width="420mm" height="297mm"/>
				<format name="A4p" width="210mm" height="297mm"/>
				<format name="A4l" width="297mm" height="210mm"/>
				<format name="A5p" width="148mm" height="210mm"/>
				<format name="A5l" width="210mm" height="148mm"/>
			</paper-formats>
		</constants-data>
	</xsl:variable>
	<xsl:variable name="docDataConstants" select="ext:node-set($docDataConstantsSrc)/constants-data"/>
	
  	<!-- <xsl:attribute-set name="paper-format-A4p">
		<xsl:attribute name="page-width"><xsl:value-of select="$docDataConstants/paper-formats/format[@name='A4p']/@width"/></xsl:attribute>
		<xsl:attribute name="page-height"><xsl:value-of select="$docDataConstants/paper-formats/format[@name='A4p']/@height"/></xsl:attribute>
	</xsl:attribute-set>
	
  	<xsl:attribute-set name="paper-format-A4l">
		<xsl:attribute name="page-width"><xsl:value-of select="$docDataConstants/paper-formats/format[@name='A4l']/@width"/></xsl:attribute>
		<xsl:attribute name="page-height"><xsl:value-of select="$docDataConstants/paper-formats/format[@name='A4l']/@height"/></xsl:attribute>
	</xsl:attribute-set> -->
	
  	<xsl:attribute-set name="page-margins---old">
		<xsl:attribute name="margin-left"  >20mm</xsl:attribute>
		<xsl:attribute name="margin-top"   >10mm</xsl:attribute>
		<xsl:attribute name="margin-right" >10mm</xsl:attribute>
		<xsl:attribute name="margin-bottom">10mm</xsl:attribute>
	</xsl:attribute-set>
	<xsl:attribute-set name="page-margins">
		<xsl:attribute name="margin-left"  >20mm</xsl:attribute>
		<xsl:attribute name="margin-top"   >20mm</xsl:attribute>
		<xsl:attribute name="margin-right" >10mm</xsl:attribute>
		<xsl:attribute name="margin-bottom">20mm</xsl:attribute>
	</xsl:attribute-set>

  	<xsl:attribute-set name="font-defaults">
		<xsl:attribute name="font-family">Times New Roman</xsl:attribute>
		<!--xsl:attribute name="font-family">Calibri</xsl:attribute-->
		<xsl:attribute name="font-size">14pt</xsl:attribute>
	</xsl:attribute-set>
	
	
	
  	<xsl:attribute-set name="title-defaults">
		<xsl:attribute name="text-align">center</xsl:attribute>
		<xsl:attribute name="space-after">12pt</xsl:attribute>
	</xsl:attribute-set>

	
	
	
  	<xsl:attribute-set name="paragraph-defaults">
		<xsl:attribute name="text-indent">12.5mm</xsl:attribute>
		<xsl:attribute name="text-align">justify</xsl:attribute>
	</xsl:attribute-set>	



  	<xsl:attribute-set name="list-marked-defaults">
		<xsl:attribute name="provisional-distance-between-starts">5mm</xsl:attribute>
		<xsl:attribute name="start-indent">12.5mm</xsl:attribute>
		<xsl:attribute name="end-indent">0mm</xsl:attribute>
	</xsl:attribute-set>

	<xsl:attribute-set name="list-numbered-defaults">
		<xsl:attribute name="start-indent">12.5mm</xsl:attribute>
		<xsl:attribute name="end-indent">0mm</xsl:attribute>
		<xsl:attribute name="provisional-label-separation">0.25em</xsl:attribute>
	</xsl:attribute-set>

	<xsl:attribute-set name="list-item-body-defaults">
		<xsl:attribute name="text-align">justify</xsl:attribute>
	</xsl:attribute-set>




	<xsl:attribute-set name="line-default">
		<xsl:attribute name="leader-pattern">rule</xsl:attribute>
		<xsl:attribute name="rule-thickness">0.5pt</xsl:attribute>
	</xsl:attribute-set>
  	<xsl:attribute-set name="line--between" use-attribute-sets="line-default">
		<xsl:attribute name="leader-length.minimum">0mm</xsl:attribute>
		<xsl:attribute name="leader-length.optimum">50%</xsl:attribute>
		<xsl:attribute name="leader-length.maximum">100%</xsl:attribute>
	</xsl:attribute-set>
  	<xsl:attribute-set name="line--full-width" use-attribute-sets="line-default">
		<xsl:attribute name="leader-length.optimum">100%</xsl:attribute>
	</xsl:attribute-set>
  	<xsl:attribute-set name="line--fixed" use-attribute-sets="line-default">
	</xsl:attribute-set>
	
	
	
	<xsl:attribute-set name="spacer-default">
		<xsl:attribute name="leader-pattern">space</xsl:attribute>
		<xsl:attribute name="leader-length.minimum">0mm</xsl:attribute>
		<xsl:attribute name="leader-length.optimum">50%</xsl:attribute>
		<xsl:attribute name="leader-length.maximum">100%</xsl:attribute>
	</xsl:attribute-set>

	<xsl:template name="str-split">
		<xsl:param name="data" select="string(.)"/>
		<xsl:param name="delim">,</xsl:param>
		<xsl:param name="tag">item</xsl:param>
		<xsl:param name="index">0</xsl:param>
		<xsl:choose>
			<xsl:when test="contains( $data , $delim )">
				<xsl:element name="{$tag}">
					<xsl:attribute name="index"><xsl:value-of select="$index"/></xsl:attribute>
					<xsl:value-of select="substring-before( $data , $delim )"/>
				</xsl:element>
				<xsl:call-template name="str-split">
					<xsl:with-param name="data" select="substring-after( $data , $delim )"/>
					<xsl:with-param name="delim" select="$delim"/>
					<xsl:with-param name="tag" select="$tag"/>
					<xsl:with-param name="index" select="$index + 1"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:element name="{$tag}">
					<xsl:attribute name="index"><xsl:value-of select="$index"/></xsl:attribute>
					<xsl:value-of select="$data"/>
				</xsl:element>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="attr--mkSimpleItemGroup">
		<xsl:param name="prefix"/>
		<xsl:param name="elements"/>
		<xsl:variable name="elTree">
			<xsl:call-template name="str-split">
				<xsl:with-param name="data" select="$elements"/>
			</xsl:call-template>
		</xsl:variable>
		<!--xsl:copy-of select="$elTree"/-->
		<xsl:for-each select="ext:node-set( $elTree )/*">
			<xsl:element name="map">
				<xsl:attribute name="name">
					<xsl:value-of select="$prefix"/>
					<xsl:value-of select="."/>
				</xsl:attribute>
				<xsl:attribute name="to-name">
					<xsl:value-of select="$prefix"/>
					<xsl:value-of select="."/>
				</xsl:attribute>
				<xsl:attribute name="copy-value">true</xsl:attribute>
			</xsl:element>
		</xsl:for-each>
	</xsl:template>

	<xsl:variable name="__text__attributeMap">
		<group name="font">
			<map name="bold"        to-name="font-weight" fixed-value="true">bold</map>
			<map name="italic"      to-name="font-style"  fixed-value="true">italic</map>
			<map name="font-size"   to-name="font-size"   copy-value="true"/>
			<map name="font-family" to-name="font-family" copy-value="true"/>
		</group>
		<group name="text">
			<map name="align"     to-name="text-align"      copy-value="true"/>
			<map name="indent"    to-name="text-indent"     copy-value="true"/>
			<map name="underline" to-name="text-decoration" fixed-value="true">underline</map>
		</group>
		<group name="box.size">
			<map name="width"  to-name="width"  copy-value="true"/>
			<map name="height" to-name="height" copy-value="true"/>
		</group>
		<group name="box.margin">
			<xsl:call-template name="attr--mkSimpleItemGroup">
				<xsl:with-param name="prefix">margin-</xsl:with-param>
				<xsl:with-param name="elements">top,right,bottom,left</xsl:with-param>
			</xsl:call-template>
		</group>
		<group name="box.padding">
			<xsl:call-template name="attr--mkSimpleItemGroup">
				<xsl:with-param name="prefix">padding-</xsl:with-param>
				<xsl:with-param name="elements">top,right,bottom,left</xsl:with-param>
			</xsl:call-template>
		</group>
		<group name="box.border">
			<map name="border"  to-name="border"  copy-value="true"/>
			<xsl:call-template name="attr--mkSimpleItemGroup">
				<xsl:with-param name="prefix">border-</xsl:with-param>
				<xsl:with-param name="elements">color,style,width,top,top-color,top-style,top-width,right,right-color,right-style,right-width,bottom,bottom-color,bottom-style,bottom-width,left,left-color,left-style,left-width</xsl:with-param>
			</xsl:call-template>
		</group>
		<group name="table-cell-span">
			<map name="colspan"  to-name="number-columns-spanned"  copy-value="true"/>
			<map name="rowspan"  to-name="number-rows-spanned"  copy-value="true"/>
		</group>
	</xsl:variable>
	<xsl:variable name="attributeMap" select="ext:node-set( $__text__attributeMap )"/>

	<xsl:variable name="__text__ElementsAttrSets">
		<element name="td">
			<xsl:copy-of select="$attributeMap/group[@name='font']/map"/>
			<xsl:copy-of select="$attributeMap/group[@name='text']/map[@name='align']"/>
			<xsl:copy-of select="$attributeMap/group[@name='text']/map[@name='underline']"/>
			<xsl:copy-of select="$attributeMap/group[@name='table-cell-span']/map"/>
			<xsl:copy-of select="$attributeMap/group[@name='box.size']/map"/>
			<xsl:copy-of select="$attributeMap/group[@name='box.margin']/map"/>
			<xsl:copy-of select="$attributeMap/group[@name='box.padding']/map"/>
			<xsl:copy-of select="$attributeMap/group[@name='box.border']/map"/>
		</element>
		<element name="td.block">
			<xsl:copy-of select="$attributeMap/group[@name='text']/map[@name='indent']"/>
		</element>
		<element name="tr">
			<xsl:copy-of select="$attributeMap/group[@name='font']/map"/>
			<xsl:copy-of select="$attributeMap/group[@name='text']/map"/>
			<xsl:copy-of select="$attributeMap/group[@name='box.size']/map"/>
			<xsl:copy-of select="$attributeMap/group[@name='box.margin']/map"/>
			<xsl:copy-of select="$attributeMap/group[@name='box.padding']/map"/>
			<xsl:copy-of select="$attributeMap/group[@name='box.border']/map"/>
		</element>
	</xsl:variable>
	<xsl:variable name="ElementsAttrSets" select="ext:node-set( $__text__ElementsAttrSets )"/>


	<xsl:template name="apply-style">
		<xsl:variable name="SN" select="@style"/>
		<!--xsl:comment>
			<xsl:copy-of select="$SN"/>
		</xsl:comment-->
		<xsl:for-each select="/doc-pack/template/styles/style[@name=$SN]/*">
			<xsl:variable name="attrName" select="@name"/>
			<xsl:variable name="attrGroup" select="@group"/>
			<xsl:variable name="attrMapData" select="ext:node-set( $attributeMap )/group[@name=$attrGroup]/map[@name=$attrName]"/>
			<xsl:choose>
				<xsl:when test="$attrMapData">
					<xsl:variable name="attrNormalName" select="$attrMapData/@to-name"/>
					<xsl:attribute name="{$attrNormalName}">
						<xsl:choose>
							<xsl:when test="$attrMapData/@copy-value='true'"><xsl:value-of select="."/></xsl:when>
							<xsl:otherwise><xsl:value-of select="$attrMapData"/></xsl:otherwise>
						</xsl:choose>
					</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="{$attrName}">
						<xsl:value-of select="."/>
					</xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:for-each>
	</xsl:template>

	<!--
			readInlineStyle( node , domCurrent , attrSet ) {
			const attrIDMap = TDGDocElementAttributes.IDMap ;
			const cas = attrSet ? attrSet : this.constructor.attrSets ;
			for( const attrID of cas ) {
				let attrData =  attrIDMap[ attrID ];
				let attrSrcName = attrData.fromName ;
				let attrNormalName = attrData.toName ;

				if ( node.hasAttribute( attrSrcName ) ) {
					if ( attrData.copyValue ) {
						domCurrent.style[ attrNormalName ] = node.getAttribute( attrSrcName );
					} else {
						domCurrent.style[ attrNormalName ] = attrData.value ;
					}
				}
			}
		}


	-->

	<xsl:template name="read-inline-style">
		<xsl:param name="element" select="."/>
		<xsl:param name="SN">p</xsl:param>
		<xsl:for-each select="$element/@*">
			<xsl:variable name="attrSrcName" select="name(.)"/>
			<xsl:variable name="attrData" select="$ElementsAttrSets/element[@name=$SN]/map[@name=$attrSrcName]"/>
			<xsl:if test="$attrData">
				<xsl:variable name="attrNormalName" select="$attrData/@to-name"/>
				<xsl:attribute name="{$attrNormalName}">
					<xsl:choose>
						<xsl:when test="$attrData/@copy-value='true'">
							<xsl:value-of select="."/>
						</xsl:when>
						<xsl:otherwise><xsl:value-of select="$attrData"/></xsl:otherwise>
					</xsl:choose>
				</xsl:attribute>
			</xsl:if>
			<!--xsl:copy-of select="$attrData"/-->
			<!--xsl:variable name="attrName" select="@name"/>
			<xsl:variable name="attrGroup" select="@group"/>
			<xsl:variable name="attrMapData" select="ext:node-set( $attributeMap )/group[@name=$attrGroup]/map[@name=$attrName]"/>
			<xsl:choose>
				<xsl:when test="$attrMapData">
					<xsl:variable name="attrNormalName" select="$attrMapData/@to-name"/>
					<xsl:attribute name="{$attrNormalName}">
						<xsl:choose>
							<xsl:when test="$attrMapData/@copy-value='true'"><xsl:value-of select="."/></xsl:when>
							<xsl:otherwise><xsl:value-of select="$attrMapData"/></xsl:otherwise>
						</xsl:choose>
					</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="{$attrName}">
						<xsl:value-of select="."/>
					</xsl:attribute>
				</xsl:otherwise>
			</xsl:choose-->
		</xsl:for-each>
	</xsl:template>



	<xsl:template match="br">
		<xsl:choose>
			<xsl:when test="name(preceding-sibling::node()[1])='br'">
				<fo:block><xsl:value-of select="'&#160;'"/></fo:block>
			</xsl:when>
			<xsl:when test="name(preceding-sibling::node()[2])='br' and (preceding-sibling::node()[1])[self::text()]  and not(normalize-space(preceding-sibling::node()[1]))">
				<fo:block><xsl:value-of select="'&#160;'"/></fo:block>
			</xsl:when>
			<xsl:otherwise>
				<fo:block/>
			</xsl:otherwise>
		</xsl:choose>
		<!--fo:block/-->
		<!--xsl:value-of select="'&#x2028;'"/-->
	</xsl:template>
	
	<!--xsl:template name="tokenize">
		<xsl:param name="csv" />
		<xsl:variable name="first-item" select="normalize-space( substring-before( concat( $csv , ' ' ) , ' ' ) )" />
		<xsl:if test="$first-item">
			<item>
				<xsl:value-of select="$first-item" />
			</item>
			<xsl:call-template name="tokenize">
				<xsl:with-param name="csv" select="substring-after( $csv , ' ' )" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template-->
	
	<xsl:template name="template-data">
		<xsl:variable name="cpfn" select="./@format"/>
		<xsl:variable name="cti" select="position()"/>
		<xsl:variable name="cpn" select="concat( 'p' , $cti )"/>
		<xsl:variable name="csn" select="concat( 'p' , $cti , 's1' )"/>
		<xsl:element name="fo:simple-page-master" use-attribute-sets="page-margins">
			<xsl:attribute name="master-name">
				<xsl:value-of select="$cpn"/>
			</xsl:attribute>
			<xsl:attribute name="page-width">
				<xsl:value-of select="$docDataConstants/paper-formats/format[@name=$cpfn]/@width"/>
			</xsl:attribute>
			<xsl:attribute name="page-height">
				<xsl:value-of select="$docDataConstants/paper-formats/format[@name=$cpfn]/@height"/>
			</xsl:attribute>
			<xsl:if test="@margin-top">
				<xsl:attribute name="margin-top">
					<xsl:value-of select="@margin-top"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="@margin-right">
				<xsl:attribute name="margin-right">
					<xsl:value-of select="@margin-right"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="@margin-bottom">
				<xsl:attribute name="margin-bottom">
					<xsl:value-of select="@margin-bottom"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="@margin-left">
				<xsl:attribute name="margin-left">
					<xsl:value-of select="@margin-left"/>
				</xsl:attribute>
			</xsl:if>

			<fo:region-body/>
		</xsl:element>
			
			<!-- <fo:simple-page-master master-name="p1" xsl:use-attribute-sets="page-format page-margins">
				<fo:region-body></fo:region-body>
			</fo:simple-page-master> -->
			
		<xsl:element name="fo:page-sequence-master">
			<xsl:attribute name="master-name">
				<xsl:value-of select="$csn"/>
			</xsl:attribute>
			<xsl:element name="fo:single-page-master-reference">
				<xsl:attribute name="master-reference">
					<xsl:value-of select="$cpn"/>
				</xsl:attribute>
			</xsl:element>
		</xsl:element>
		<!-- 
		<fo:page-sequence-master master-name="s1">
			<fo:single-page-master-reference master-reference="p1"/>
		</fo:page-sequence-master>
		 -->
	</xsl:template>

	<xsl:template match="/doc-pack">
		<!--test>
			<xsl:comment>$attributeMap</xsl:comment>
			<xsl:copy-of select="$attributeMap"/>
			<xsl:comment>$ElementsAttrSets</xsl:comment>
			<xsl:copy-of select="$ElementsAttrSets/element[@name='td']"/>
			<xsl:call-template name="read-inline-style">
				<xsl:with-param name="element" select="."/>
				<xsl:with-param name="SN">td</xsl:with-param>
			</xsl:call-template>
		</test-->
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<xsl:for-each select="/doc-pack/template">
					<xsl:call-template name="template-data"/>
				</xsl:for-each>
			</fo:layout-master-set>
			<xsl:apply-templates select="/doc-pack/template"/>
		</fo:root>
	</xsl:template>
	
	<xsl:template match="/doc-pack/template">
		<xsl:variable name="cti" select="position()"/>
		<xsl:variable name="cpn" select="concat( 'p' , $cti )"/>
		<xsl:variable name="csn" select="concat( 'p' , $cti , 's1' )"/>
		<xsl:element name="fo:page-sequence">
			<xsl:attribute name="master-reference">
				<xsl:value-of select="$csn"/>
			</xsl:attribute>
			<fo:static-content flow-name="xsl-footnote-separator">
				<fo:block>
					<fo:leader leader-pattern="rule" leader-length="30%" rule-style="solid" rule-thickness="0.5pt"/>
				</fo:block>
			</fo:static-content>
			<fo:flow flow-name="xsl-region-body" xsl:use-attribute-sets="font-defaults">
				<xsl:apply-templates/>
			</fo:flow >
		</xsl:element>
		<!--
		<fo:page-sequence master-reference="s1">
			<fo:flow flow-name="xsl-region-body" xsl:use-attribute-sets="font-defaults">
				<xsl:apply-templates select="/doc-pack/template"/>
			</fo:flow >
		</fo:page-sequence >		
		-->
	</xsl:template>
	
	<xsl:template match="/doc-pack/template/title">
		<xsl:element name="fo:block" use-attribute-sets="title-defaults">
			<xsl:call-template name="read-font-data"/>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="/doc-pack/template/title[@type='caps']/text()">
		<xsl:value-of select="translate( . , $lowercase, $uppercase)" />
		<!--xsl:call-template name="tokenize"> 
			<xsl:with-param name="csv" select="." /> 
		</xsl:call-template-->
	</xsl:template>
		
	<xsl:template match="/doc-pack/template/main-text">
		<fo:block>
			<xsl:apply-templates/>
		</fo:block>
	</xsl:template>

	<xsl:template name="read-margins">
		<xsl:if test="@margin-top">
			<xsl:attribute name="margin-top"><xsl:value-of select="@margin-top"/></xsl:attribute>
		</xsl:if>
		<xsl:if test="@margin-bottom">
			<xsl:attribute name="margin-bottom"><xsl:value-of select="@margin-bottom"/></xsl:attribute>
		</xsl:if>
		<xsl:if test="@margin-left">
			<xsl:attribute name="margin-left"><xsl:value-of select="@margin-left"/></xsl:attribute>
		</xsl:if>
		<xsl:if test="@margin-right">
			<xsl:attribute name="margin-right"><xsl:value-of select="@margin-right"/></xsl:attribute>
		</xsl:if>
	</xsl:template>

	<xsl:template name="read-font-data">
		<xsl:if test="@bold">
			<xsl:attribute name="font-weight">bold</xsl:attribute>
		</xsl:if>
		<xsl:if test="@italic">
			<xsl:attribute name="font-style">italic</xsl:attribute>
		</xsl:if>
		<xsl:if test="@font-size">
			<xsl:attribute name="font-size"><xsl:value-of select="@font-size"/></xsl:attribute>
		</xsl:if>
		<xsl:if test="@font-family">
			<xsl:attribute name="font-family"><xsl:value-of select="@font-family"/></xsl:attribute>
		</xsl:if>
	</xsl:template>

	<xsl:template match="p">
		<xsl:element name="fo:block" use-attribute-sets="paragraph-defaults">
			<xsl:call-template name="apply-style"/>
			<xsl:if test="@indent">
				<xsl:attribute name="text-indent"><xsl:value-of select="@indent"/></xsl:attribute>
			</xsl:if>
			<!--xsl:if test="@indent-right">
				<xsl:attribute name="text-indent"><xsl:value-of select="@indent"/></xsl:attribute>
			</xsl:if-->
			<xsl:if test="@align">
				<xsl:attribute name="text-align"><xsl:value-of select="@align"/></xsl:attribute>
			</xsl:if>
			<xsl:call-template name="read-font-data"/>
			<xsl:call-template name="read-margins"/>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>
	
	<!--
		<line style="between" />
	-->
	<xsl:template match="line[@style='between']">
		<fo:leader xsl:use-attribute-sets="line--between"/>
	</xsl:template>

	<!--
		<line style="full-width" />
	-->	
	<xsl:template match="line[@style='full-width']">
		<fo:leader xsl:use-attribute-sets="line--full-width"/>
	</xsl:template>
	
	<!--
		<line style="fixed" width="20mm" />
		<xsl:attribute name="leader-length.minimum">0mm</xsl:attribute>
		<xsl:attribute name="leader-length.optimum">50%</xsl:attribute>
		<xsl:attribute name="leader-length.maximum">100%</xsl:attribute>

	-->
	<xsl:template match="line[@style='fixed']">
		<xsl:element name="fo:leader" use-attribute-sets="line--fixed">
			<xsl:attribute name="leader-length.minimum"><xsl:value-of select="@width"/></xsl:attribute>
			<xsl:attribute name="leader-length.optimum"><xsl:value-of select="@width"/></xsl:attribute>
			<xsl:attribute name="leader-length.maximum"><xsl:value-of select="@width"/></xsl:attribute>
		</xsl:element>
	</xsl:template>	
	
	<!--
		<spacer />
	-->
	<xsl:template match="spacer">
		<xsl:element name="fo:leader" use-attribute-sets="spacer-default">
			<xsl:choose>
				<xsl:when test="./@min-width and ./@width and ./@max-width">
					<xsl:attribute name="leader-length.minimum"><xsl:value-of select="@min-width"/></xsl:attribute>
					<xsl:attribute name="leader-length.optimum"><xsl:value-of select="@width"/></xsl:attribute>
					<xsl:attribute name="leader-length.maximum"><xsl:value-of select="@max-width"/></xsl:attribute>
				</xsl:when>
				<xsl:when test="./@width">
					<xsl:attribute name="leader-length.minimum"><xsl:value-of select="@width"/></xsl:attribute>
					<xsl:attribute name="leader-length.optimum"><xsl:value-of select="@width"/></xsl:attribute>
					<xsl:attribute name="leader-length.maximum"><xsl:value-of select="@width"/></xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:element>
		<!--fo:leader xsl:use-attribute-sets="spacer-default"/-->
	</xsl:template>



	<!--
		<b>...</b>
	-->
	<xsl:template match="b">
		<fo:inline font-weight="bold">
			<xsl:apply-templates/>
		</fo:inline>
	</xsl:template>

	<xsl:template match="b0">
		<fo:inline font-weight="normal">
			<xsl:apply-templates/>
		</fo:inline>
	</xsl:template>

	<!--
		<i>...</i>
	-->
	<xsl:template match="i">
		<fo:inline font-style="italic">
			<xsl:apply-templates/>
		</fo:inline>
	</xsl:template>

	<!--
		<u>...</u>
	-->
	<xsl:template match="u">
		<fo:inline text-decoration="underline">
			<xsl:apply-templates/>
		</fo:inline>
	</xsl:template>

	<!--
		<hl>...</hl>
	-->
	<xsl:template match="hl">
		<xsl:element name="fo:inline">
			<xsl:attribute name="color">
				<xsl:choose>
					<xsl:when test="@color"><xsl:value-of select="@color"/></xsl:when>
					<xsl:otherwise>#dddddd</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>


	<xsl:template match="mark">
		<xsl:element name="fo:inline">
			<xsl:attribute name="background-color">
				<xsl:choose>
					<xsl:when test="@color"><xsl:value-of select="@color"/></xsl:when>
					<xsl:otherwise>#dddddd</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>

	<!--
		<font>...</font>
	-->

	<xsl:template match="font">
		<xsl:element name="fo:inline">
			<xsl:if test="@size">
				<xsl:attribute name="font-size">
					<xsl:value-of select="@size"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="@family">
				<xsl:attribute name="font-family">
					<xsl:value-of select="@family"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>

	<!--
		<case type="upper">...</case>
	-->

	<xsl:template name="tree-process-case">
		<xsl:param name="srcNode" select="."/>
		<xsl:param name="newCase" select="'upper'"/>
		<xsl:comment>
			<xsl:value-of select="$newCase"/>
		</xsl:comment>
		<xsl:for-each select="ext:node-set( $srcNode )/node()">
			<xsl:choose>
				<xsl:when test="self::text()">
					<xsl:choose>
						<xsl:when test="$newCase = 'upper'">
							<xsl:value-of select="translate( . , $lowercase , $uppercase )" />
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="translate( . , $uppercase , $lowercase )" />
						</xsl:otherwise>
					</xsl:choose>
				</xsl:when>
				<xsl:when test="self::*">
					<xsl:variable name="elName" select="name(.)"/>
					<xsl:element name="{$elName}">
						<xsl:for-each select="./@*">
							<xsl:variable name="attrName" select="name(.)"/>
							<xsl:attribute name="{$attrName}">
								<xsl:value-of select="."/>
							</xsl:attribute>
						</xsl:for-each>
						<xsl:call-template name="tree-process-case">
							<xsl:with-param name="srcNode" select="."/>
							<xsl:with-param name="newCase" select="$newCase"/>
						</xsl:call-template>
					</xsl:element>
				</xsl:when>
			</xsl:choose>
		</xsl:for-each>
	</xsl:template>

	<xsl:template match="case[@type='upper' or @type='lower']">
		<fo:inline>
			<xsl:call-template name="tree-process-case">
				<xsl:with-param name="srcNode">
					<xsl:apply-templates/>
				</xsl:with-param>
				<xsl:with-param name="newCase" select="@type"/>
			</xsl:call-template>
		</fo:inline>
	</xsl:template>


	<!--
		<list >...</list>
	-->
	<xsl:template match="list[not(@type) or @type='marked']">
		<xsl:if test="count(./item) > 0">
			<xsl:variable name="CurrentListMarker">
				<xsl:choose>
					<xsl:when test="@marker"><xsl:value-of select="@marker"/></xsl:when>
					<xsl:otherwise>-</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			<xsl:variable name="CurrentListSeparator">
				<xsl:choose>
					<xsl:when test="@separator"><xsl:value-of select="@separator"/></xsl:when>
					<xsl:otherwise>;</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			<xsl:variable name="CurrentListFinisher">
				<xsl:choose>
					<xsl:when test="@finisher"><xsl:value-of select="@finisher"/></xsl:when>
					<xsl:otherwise>.</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			<xsl:element name="fo:list-block" use-attribute-sets="list-marked-defaults">
				<xsl:apply-templates select="./item">
					<xsl:with-param name="ListMarker" select="$CurrentListMarker"/>
					<xsl:with-param name="ListSeparator" select="$CurrentListSeparator"/>
					<xsl:with-param name="ListFinisher" select="$CurrentListFinisher"/>
				</xsl:apply-templates>
			</xsl:element>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="list[not(@type) or @type='marked']/item">
		<xsl:param name="ListMarker"    select="0" />
		<xsl:param name="ListSeparator" select="0" />
		<xsl:param name="ListFinisher"  select="0" />
		<fo:list-item>
			<fo:list-item-label end-indent="label-end()"><fo:block><xsl:value-of select="$ListMarker"/></fo:block></fo:list-item-label>
			<fo:list-item-body start-indent="body-start()"><xsl:element name="fo:block" use-attribute-sets="list-item-body-defaults">
				<xsl:variable name="subs">
					<xsl:apply-templates/>
				</xsl:variable>
				<xsl:value-of select="$subs"/>
				<xsl:choose>
					<xsl:when test=". = ../item[last()]">
						<xsl:if test="substring( $subs , string-length( $subs ) ) != $ListFinisher">
							<xsl:value-of select="$ListFinisher"/>
						</xsl:if>
					</xsl:when>
					<xsl:otherwise>
						<xsl:if test="substring( $subs , string-length( $subs ) ) != $ListSeparator">
							<xsl:value-of select="$ListSeparator"/>
						</xsl:if>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:element></fo:list-item-body>
		</fo:list-item>
	</xsl:template>

	<xsl:template match="list[@type='numbered']">
		<xsl:if test="count(./item) > 0">
			<xsl:variable name="CurrentListSeparator">
				<xsl:choose>
					<xsl:when test="@separator"><xsl:value-of select="@separator"/></xsl:when>
					<xsl:otherwise>;</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			<xsl:variable name="CurrentListFinisher">
				<xsl:choose>
					<xsl:when test="@finisher"><xsl:value-of select="@finisher"/></xsl:when>
					<xsl:otherwise>.</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			<xsl:choose>
				<xsl:when test="@aligned">
					<xsl:element name="fo:list-block" use-attribute-sets="list-numbered-defaults">
						<xsl:apply-templates select="./item">
							<xsl:with-param name="ListSeparator" select="$CurrentListSeparator"/>
							<xsl:with-param name="ListFinisher" select="$CurrentListFinisher"/>
						</xsl:apply-templates>
					</xsl:element>
				</xsl:when>
				<xsl:otherwise>
					<xsl:apply-templates select="./item">
						<xsl:with-param name="ListSeparator" select="$CurrentListSeparator"/>
						<xsl:with-param name="ListFinisher" select="$CurrentListFinisher"/>
					</xsl:apply-templates>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
	</xsl:template>

	<xsl:template match="list[@type='numbered' and @aligned]/item">
		<xsl:param name="ListSeparator" select="0" />
		<xsl:param name="ListFinisher"  select="0" />
		<fo:list-item>
			<fo:list-item-label end-indent="label-end()"><fo:block><xsl:number format="1. " count="item"/></fo:block></fo:list-item-label>
			<fo:list-item-body start-indent="body-start()"><xsl:element name="fo:block" use-attribute-sets="list-item-body-defaults">
			<xsl:apply-templates/>
				<xsl:choose>
					<xsl:when test=". = ../item[last()]"><xsl:value-of select="$ListFinisher"/></xsl:when>
					<xsl:otherwise><xsl:value-of select="$ListSeparator"/></xsl:otherwise>
				</xsl:choose>
			</xsl:element></fo:list-item-body>
		</fo:list-item>
	</xsl:template>

	<xsl:template match="list[@type='numbered' and not(@aligned)]/item">
		<xsl:param name="ListSeparator" select="0" />
		<xsl:param name="ListFinisher"  select="0" />
		<xsl:element name="fo:block" use-attribute-sets="paragraph-defaults">
			<fo:inline padding-right="2.5mm"><xsl:number format="1." count="item"/></fo:inline>
			<fo:inline>
				<xsl:apply-templates/>
				<xsl:choose>
					<xsl:when test=". = ../item[last()]"><xsl:value-of select="$ListFinisher"/></xsl:when>
					<xsl:otherwise><xsl:value-of select="$ListSeparator"/></xsl:otherwise>
				</xsl:choose>
			</fo:inline>
		</xsl:element>
	</xsl:template>

	<xsl:template match="list-item">
		<xsl:variable name="ListName" select="@list"/>
		<xsl:variable name="NumFormat">
			<xsl:choose>
				<xsl:when test="@format"><xsl:value-of select="@format"/></xsl:when>
				<xsl:otherwise>1.</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:element name="fo:inline">
			<xsl:if test="@space-after">
				<xsl:attribute name="padding-right"><xsl:value-of select="@space-after"/></xsl:attribute>
			</xsl:if>
			<xsl:number level="any" format="{$NumFormat}" count="list-item[@list=$ListName]"/>
		</xsl:element>
	</xsl:template>



	<xsl:template match="block">
		<xsl:element name="fo:block">
			<xsl:if test="@width">
				<xsl:attribute name="width"><xsl:value-of select="@width"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="@font-size">
				<xsl:attribute name="font-size"><xsl:value-of select="@font-size"/></xsl:attribute>
			</xsl:if>
			<xsl:choose>
				<xsl:when test="@pagebreak and @pagebreak = 'before'">
					<xsl:attribute name="page-break-before">always</xsl:attribute>
				</xsl:when>
				<xsl:when test="@pagebreak and @pagebreak = 'after'">
					<xsl:attribute name="page-break-after">always</xsl:attribute>
				</xsl:when>
			</xsl:choose>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="inline">
		<xsl:choose>
			<xsl:when test="@width">
				<xsl:element name="fo:inline-container">
					<xsl:attribute name="width"><xsl:value-of select="@width"/></xsl:attribute>
					<xsl:element name="fo:block">
						<xsl:apply-templates/>
					</xsl:element>
				</xsl:element>
			</xsl:when>
			<xsl:otherwise>
				<xsl:element name="fo:inline">
					<xsl:apply-templates/>
				</xsl:element>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="image">
		<fo:block>
			<xsl:element name="fo:external-graphic">
				<xsl:choose>
					<xsl:when test="./@type='dmtx'">
						<xsl:attribute name="src">
							<xsl:text>url(post-data:</xsl:text><xsl:value-of select="./@id"/><xsl:text>.png)</xsl:text>
						</xsl:attribute>
					</xsl:when>
					<xsl:when test="./@type='user-def'">
						<xsl:attribute name="src">
							<xsl:text>url(post-data:</xsl:text><xsl:value-of select="./@id"/><xsl:text>)</xsl:text>
						</xsl:attribute>
					</xsl:when>
					<xsl:otherwise>
						<xsl:attribute name="src"><xsl:value-of select="@src"/></xsl:attribute>
					</xsl:otherwise>
				</xsl:choose>

				<xsl:if test="./@width">
					<xsl:attribute name="width"><xsl:value-of select="./@width"/></xsl:attribute>
				</xsl:if>
				<xsl:if test="./@height">
					<xsl:attribute name="height"><xsl:value-of select="./@height"/></xsl:attribute>
				</xsl:if>
				<xsl:if test="./@width and ./@height">
					<xsl:attribute name="scaling">non-uniform</xsl:attribute>
				</xsl:if>
				<xsl:if test="./@max-width">
					<xsl:attribute name="width"><xsl:value-of select="./@max-width"/></xsl:attribute>
				</xsl:if>
				<xsl:if test="./@max-height">
					<xsl:attribute name="height"><xsl:value-of select="./@max-height"/></xsl:attribute>
				</xsl:if>
				<xsl:attribute name="content-width">scale-to-fit</xsl:attribute>
				<xsl:attribute name="content-height">scale-to-fit</xsl:attribute>
			</xsl:element>
		</fo:block>
	</xsl:template>



	<xsl:template match="table">
		<!-- <xsl:element name="fo:table">
			<xsl:attribute name="table-layout">fixed</xsl:attribute>
			<xsl:attribute name="width">100%</xsl:attribute>
			<xsl:attribute name="border-collapse">collapse</xsl:attribute>
			<xsl:apply-templates/>
		</xsl:element> -->
		<!-- 2023-05-25 17:52 <fo:table table-layout="fixed" width="100%" border-collapse="collapse"> -->
		<xsl:element name="fo:table">
			<xsl:attribute name="table-layout">fixed</xsl:attribute>
			<xsl:choose>
				<xsl:when test="@width">
					<xsl:attribute name="width"><xsl:value-of select="@width"/></xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					 <xsl:attribute name="width">100%</xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>
			<fo:table-body>
				<xsl:apply-templates />
			</fo:table-body>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="tr">
		<xsl:element name="fo:table-row">
			<xsl:if test="@height">
				<xsl:attribute name="height"><xsl:value-of select="@height"/></xsl:attribute>
			</xsl:if>
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="td">
		<xsl:variable name="currentElement" select="."/>
		<xsl:variable name="parentTable" select="ancestor::table[1]"/>
		<!--xsl:comment><xsl:value-of select="name( $parenTable )"/></xsl:comment-->
		<xsl:element name="fo:table-cell">
			<xsl:if test="$parentTable/colpar and not(@colspan)">
				<xsl:variable name="colIndex" select="count(preceding-sibling::td) + 1"/>
				<xsl:variable name="colParCOL" select="($parentTable/colpar/col)[$colIndex]"/>
				<xsl:if test="$colParCOL">
					<!--xsl:copy-of select="$colParCOL"/-->
					<xsl:call-template name="read-inline-style">
						<xsl:with-param name="element" select="$colParCOL"/>
						<xsl:with-param name="SN">td</xsl:with-param>
					</xsl:call-template>
				</xsl:if>
			</xsl:if>
			<xsl:if test="@colspantarget">
				<xsl:attribute name="number-columns-spanned">
					<xsl:value-of select="count(./to-span)"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:call-template name="apply-style"/>
			<xsl:call-template name="read-inline-style">
				<xsl:with-param name="element" select="$currentElement"/>
				<xsl:with-param name="SN">td</xsl:with-param>
			</xsl:call-template>
			<xsl:variable name="aValign">
				<xsl:choose>
					<xsl:when test="@valign">
						<xsl:value-of select="@valign"/>
					</xsl:when>
					<xsl:when test="../@valign">
						<xsl:value-of select="../@valign"/>
					</xsl:when>
					<xsl:otherwise>center</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			<xsl:if test="$aValign">
				<xsl:choose>
					<xsl:when test="$aValign = 'top'">
						<xsl:attribute name="display-align">before</xsl:attribute>
					</xsl:when>
					<xsl:when test="$aValign = 'center'">
						<xsl:attribute name="display-align">center</xsl:attribute>
					</xsl:when>
					<xsl:when test="$aValign = 'middle'">
						<xsl:attribute name="display-align">center</xsl:attribute>
					</xsl:when>
					<xsl:when test="$aValign = 'bottom'">
						<xsl:attribute name="display-align">after</xsl:attribute>
					</xsl:when>
				</xsl:choose>
			</xsl:if>
			<xsl:element name="fo:block">
				<xsl:if test="$parentTable/colpar and not(@colspan)">
					<xsl:variable name="colIndex" select="count(preceding-sibling::td) + 1"/>
					<xsl:variable name="colParCOL" select="($parentTable/colpar/col)[$colIndex]"/>
					<xsl:if test="$colParCOL">
						<!--xsl:copy-of select="$colParCOL"/-->
						<xsl:call-template name="read-inline-style">
							<xsl:with-param name="element" select="$colParCOL"/>
							<xsl:with-param name="SN">td.block</xsl:with-param>
						</xsl:call-template>
					</xsl:if>
				</xsl:if>
				<xsl:call-template name="read-inline-style">
					<xsl:with-param name="element" select="$currentElement"/>
					<xsl:with-param name="SN">td.block</xsl:with-param>
				</xsl:call-template>
				<xsl:apply-templates />
			</xsl:element>
		</xsl:element>
	</xsl:template>

	<xsl:template match="//footnote">
		<xsl:variable name="indexValue">
			<xsl:apply-templates select="index"/>
		</xsl:variable>
		<xsl:element name="fo:footnote">
			<fo:inline>
				<xsl:apply-templates select="element"/>
				<fo:inline baseline-shift="super" font-size="50%">
					<xsl:copy-of select="$indexValue"/>
				</fo:inline>
			</fo:inline>
			<xsl:element name="fo:footnote-body">
				<fo:block text-indent="0">
					<fo:inline baseline-shift="super" font-size="65%">
						<xsl:copy-of select="$indexValue"/>
						<xsl:text>&#160;</xsl:text>
					</fo:inline>
					<xsl:apply-templates select="comment"/>
				</fo:block>
			</xsl:element>
		</xsl:element>
	</xsl:template>



	<!--
	<xsl:template match="//doc-1/sub-caption/item[@type='place']" foa:name="agr-sub-cap-place" foa:group="float" foa:class="float" >
		<fo:float foa:name="agr-sub-cap-place" foa:group="float" foa:class="float" foa:content="static" xsl:use-attribute-sets=" agr-sub-cap-place-left">
			<fo:block>
				<xsl:apply-templates/>
			</fo:block>
		</fo:float>
	</xsl:template>
	<xsl:template match="//doc-1/sub-caption/item[@type='date']" foa:name="agr-sub-cap-date" foa:group="float" foa:class="float" >
		<fo:float foa:name="agr-sub-cap-date" foa:group="float" foa:class="float" foa:content="static" xsl:use-attribute-sets=" agr-sub-cap-date-right">
			<fo:block>
				<xsl:apply-templates/>
			</fo:block>
		</fo:float>
	</xsl:template>
	<xsl:template match="//doc-1/caption/line" foa:name="agr-cap" foa:group="paragraph" foa:class="block" >
		<fo:block foa:name="agr-cap" foa:group="paragraph" foa:class="block" foa:content="static" xsl:use-attribute-sets=" agr-cap">
			<xsl:apply-templates/>
		</fo:block>
	</xsl:template>
	<xsl:template match="//doc-1/sub-caption" foa:name="agr-sub-cap" foa:group="paragraph" foa:class="block" >
		<fo:block foa:name="agr-sub-cap" foa:group="paragraph" foa:class="block" foa:content="static" xsl:use-attribute-sets=" agr-sub-cap">
			<xsl:apply-templates select="document('user-doc.xml')//doc-1/sub-caption/item"/>
		</fo:block>
	</xsl:template>
	<xsl:template match="//doc-1/sub-caption/item" foa:name="agr-sub-cap-items" foa:group="emphasis" foa:class="inline" >
		<fo:inline foa:name="agr-sub-cap-items" foa:group="emphasis" foa:class="inline" foa:content="static" xsl:use-attribute-sets=" agr-sub-cap-items">
			<xsl:apply-templates/>
		</fo:inline>
	</xsl:template> -->
</xsl:stylesheet>