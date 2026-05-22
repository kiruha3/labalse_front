<?xml version="1.0" encoding="UTF-8" ?>
<xsl:transform xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ext="http://exslt.org/common" xmlns:php="http://php.net/xsl" version="1.0">
	<xsl:output method="xml" encoding="UTF-8" indent="yes" />
	<xsl:variable name="docDataSrc">
		<variables></variables>
	</xsl:variable>

	<xsl:variable name="docData" select="ext:node-set( $docDataSrc )" />

	<xsl:template name="inForm-avt">
		<xsl:param name="src" select="''"/>
		<xsl:param name="mode" select="0"/>
		<xsl:param name="cf" select="1"/>
		<xsl:param name="tf" select="0"/>
		<xsl:param name="ts" select="1"/>
		<xsl:choose>
			<xsl:when test="$mode = 0">
				<xsl:if test="string-length( $src ) &gt; 0">
					<xsl:choose>
						<xsl:when test="substring-before( $src , '}' ) = ''">
							<xsl:value-of select="$src"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="substring-before( $src , '{' )"/>
							
							<xsl:variable name="PaOB" select="substring-after( $src , '{' )"/>
							<xsl:variable name="PbCB" select="substring-before( $PaOB , '}' )"/>
							
							<xsl:choose>
								<xsl:when test="string-length( substring-before( $PbCB , '^' ) ) = 0">
									<xsl:call-template name="inForm-avt">
										<xsl:with-param name="src" select="$PbCB"/>
										<xsl:with-param name="tf" select="$tf"/>
										<xsl:with-param name="mode" select="1"/>
									</xsl:call-template>
								</xsl:when>
								<xsl:otherwise>
									<xsl:choose>
										<xsl:when test="$ts = 1">
											<xsl:call-template name="inForm-avt">
												<xsl:with-param name="src" select="substring-before( $PbCB , '^' )"/>
												<xsl:with-param name="tf" select="$tf"/>
												<xsl:with-param name="mode" select="1"/>
											</xsl:call-template>
										</xsl:when>
										<xsl:otherwise>
											<xsl:call-template name="inForm-avt">
												<xsl:with-param name="src" select="substring-after( $PbCB , '^' )"/>
												<xsl:with-param name="tf" select="$tf"/>
												<xsl:with-param name="mode" select="1"/>
											</xsl:call-template>
										</xsl:otherwise>
									</xsl:choose>
								</xsl:otherwise>
							</xsl:choose>
							
							<xsl:call-template name="inForm-avt">
								<xsl:with-param name="src" select="substring-after( $PaOB , '}' )"/>
								<xsl:with-param name="tf" select="$tf"/>
								<xsl:with-param name="ts" select="$ts"/>
							</xsl:call-template>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
			</xsl:when>
			<xsl:when test="$mode = 1">
				<xsl:choose>
					<xsl:when test="$cf = $tf">
						<xsl:choose>
							<xsl:when test="$cf = 6">
								<xsl:value-of select="$src"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="substring-before( $src , '|' )"/>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>
						<xsl:call-template name="inForm-avt">
							<xsl:with-param name="src" select="substring-after( $src , '|' )"/>
							<xsl:with-param name="tf" select="$tf"/>
							<xsl:with-param name="cf" select="$cf + 1"/>
							<xsl:with-param name="mode" select="1"/>
						</xsl:call-template>						
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="inForm">
		<xsl:param name="src" select="''" />
		<xsl:param name="form" select="1" />
		<xsl:param name="singularity" select="1"/>
		<xsl:call-template name="inForm-avt">
			<xsl:with-param name="src" select="$src"/>
			<xsl:with-param name="tf" select="$form"/>
			<xsl:with-param name="ts" select="$singularity"/>
		</xsl:call-template>
	</xsl:template>
		
	<xsl:template match="node()|@*">
		<xsl:copy>
			<xsl:apply-templates select="node()|@*"/>
		</xsl:copy>
	</xsl:template>

	<xsl:template name="OutputVar">
		<xsl:param name="TagData"/>
		<xsl:param name="VarData"/>
		<xsl:variable name="VarDataText">
			<xsl:value-of select="$VarData"/>
		</xsl:variable>
		<!--xsl:copy-of select="$VarData"/-->
		<xsl:variable name="VarType">
			<xsl:choose>
				<xsl:when test="$TagData/@type"><xsl:value-of select="$TagData/@type"/></xsl:when>
				<xsl:otherwise><xsl:value-of select="$VarData/@type"/></xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:comment>type : <xsl:value-of select="$VarType"/></xsl:comment>
		<xsl:comment>format : <xsl:value-of select="$TagData/@format"/></xsl:comment>
		<xsl:variable name="VarValue">
			<xsl:choose>
				<xsl:when test="$VarType='date-time'">
					<xsl:value-of select="php:function( 'tmpl2Doc_formatDate' , $VarDataText , string( $TagData/@format ) , 'utf8' )"/>
				</xsl:when>
				<xsl:when test="$VarType='variant'">
					<xsl:value-of select="$VarData/option"/>
				</xsl:when>
				<xsl:when test="$VarType='price'">
					<xsl:value-of select="php:function( 'tmpl2Doc_formatPrice' , $VarDataText , string( $TagData/@format ) , 'utf8' )"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:variable name="VarForm">
						<xsl:choose>
							<xsl:when test="$TagData/@form"><xsl:value-of select="$TagData/@form"/></xsl:when>
							<xsl:otherwise>1</xsl:otherwise>
						</xsl:choose>
					</xsl:variable>
					<xsl:choose>
						<xsl:when test="$VarData/@mf">
							<xsl:call-template name="inForm">
								<xsl:with-param name="src" select="$VarData"/>
								<xsl:with-param name="form" select="$VarForm"/>
							</xsl:call-template>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="$VarData"/>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:choose>
			<xsl:when test="$TagData/@hl">
				<xsl:element name="hl">
					<xsl:attribute name="color">
						<xsl:choose>
							<xsl:when test="$TagData/@hl = 'r'">#ff0000</xsl:when>
							<xsl:when test="$TagData/@hl = 'g'">#00cc00</xsl:when>
							<xsl:when test="$TagData/@hl = 'b'">#0088ff</xsl:when>
						</xsl:choose>
					</xsl:attribute>
					<xsl:value-of select="$VarValue"/>
				</xsl:element>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$VarValue"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="var[@name]">
		<xsl:variable name="currentVar" select="$docData/variables/var[@name=current()/@name]"/>
		<xsl:call-template name="OutputVar">
			<xsl:with-param name="TagData" select="." />
			<xsl:with-param name="VarData" select="$currentVar"/>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="var[@path]">
		<xsl:variable name="currentVar" select="$docData/variables//*[@path=current()/@path]"/>
		<xsl:call-template name="OutputVar">
			<xsl:with-param name="TagData" select="." />
			<xsl:with-param name="VarData" select="$currentVar"/>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="//dyna-con[./@name or ./@path]">
		<xsl:variable name="CurrentNodeData" select="./*" />
		<xsl:variable name="DynaConVarName" select="./@name" />
		<xsl:variable name="DynaConVarPath" select="./@path" />
		<xsl:variable name="DynaConElID" select="generate-id(.)" />
		<xsl:variable name="DynaConVarContent">
			<xsl:choose>
				<xsl:when test="./@name">
					<xsl:choose>
						<xsl:when test="./@internal">
							<xsl:choose>
								<xsl:when test="$DynaConVarName='.'">
									<xsl:copy-of select="(ancestor-or-self::*)[1]/sub-var-row/*" />
								</xsl:when>
								<xsl:otherwise>
									<xsl:copy-of select="(ancestor-or-self::*)[1]/sub-var-list/*[name(.)=$DynaConVarName]/*" />
								</xsl:otherwise>
							</xsl:choose>
						</xsl:when>
						<xsl:otherwise>
							<xsl:choose>
								<xsl:when test="$DynaConVarName='/'">
									<xsl:copy-of select="$docData/variables/*" />
								</xsl:when>
								<xsl:otherwise>
									<xsl:copy-of select="$docData/variables/var[@name=$DynaConVarName]/*" />
								</xsl:otherwise>
							</xsl:choose>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:when>
				<xsl:when test="./@path">
					<xsl:copy-of select="$docData/variables//*[@path=$DynaConVarPath]/*" />
				</xsl:when>
			</xsl:choose>
		</xsl:variable>
		<xsl:for-each select="ext:node-set( $DynaConVarContent )/*">
			<xsl:variable name="DCNN" select="name(.)" />
			<xsl:variable name="DCNID" select="./@id" />
			<xsl:variable name="NEWNODE">
				<xsl:element name="NEWNODE">
					<xsl:attribute name="dcID"><xsl:value-of select="$DynaConElID"/></xsl:attribute>
					<sub-var-list><xsl:copy-of select="./*"/></sub-var-list>
					<sub-var-row><xsl:copy-of select="@*|node()"/></sub-var-row>
					<sub-var-row-name><xsl:value-of select="$DCNN"/></sub-var-row-name>
					<sub-var-row-id><xsl:value-of select="./@id"/></sub-var-row-id>
					<sub-var-row-index><xsl:value-of select="position()"/></sub-var-row-index>
					<sub-template>
						<xsl:choose>
							<xsl:when test="./@id">
								<xsl:copy-of select="($CurrentNodeData[@name=$DCNN and @id=$DCNID]/node())[self::text() or self::*]"/>
								<xsl:copy-of select="($CurrentNodeData[@name=$DCNN and not(@id)]/node())[self::text() or self::*]"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:copy-of select="($CurrentNodeData[@name=$DCNN]/node())[self::text() or self::*]"/>
							</xsl:otherwise>
						</xsl:choose>
					</sub-template>
				</xsl:element>
			</xsl:variable>
			<xsl:apply-templates select="(ext:node-set( $NEWNODE )/NEWNODE/sub-template/node())[self::text() or self::*]"/>
		</xsl:for-each>
	</xsl:template>

	<xsl:template match="//dyna-con[@type='optional']">
		<xsl:variable name="CurrentNodeData" select="./*" />
		<xsl:variable name="DynaConVarName"><xsl:value-of select="./@name" /></xsl:variable>
		<xsl:variable name="DynaConVarContent" select="$docData/variables/var[@name=$DynaConVarName]/*" />
		<xsl:for-each select="$CurrentNodeData">
			<xsl:variable name="DCEID" select="./@id" />
			<xsl:if test="$DynaConVarContent[@id=$DCEID]">
				<xsl:apply-templates select="./*"/>
			</xsl:if>
		</xsl:for-each>
	</xsl:template>


	<xsl:template match="//sub-var">
		<xsl:variable name="CSVN" select="@name"/>
		<xsl:choose>
			<xsl:when test="$CSVN='.'">
				<xsl:comment>this node</xsl:comment>
				<xsl:call-template name="OutputVar">
					<xsl:with-param name="TagData" select="." />
					<xsl:with-param name="VarData" select="(ancestor-or-self::*)[1]/sub-var-row"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="OutputVar">
					<xsl:with-param name="TagData" select="." />
					<xsl:with-param name="VarData" select="(ancestor-or-self::*)[1]/sub-var-list/*[name(.)=$CSVN]"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="//dyna-con-index">
		<xsl:value-of select="/NEWNODE/sub-var-row-index"/>
	</xsl:template>

	<xsl:template match="//calcGgGg">
		<xsl:variable name="NODES">
			<xsl:copy-of select="$docData/variables"/>
		</xsl:variable>
		<xsl:value-of select="php:function( 'tmpl2Doc_calc' , ext:node-set($NODES) )"/>
	</xsl:template>

	<xsl:template match="//calculate">
		<xsl:comment>MAKE CALCULATION</xsl:comment>
		<xsl:variable name="data">
			<xsl:apply-templates select="./*"/>
		</xsl:variable>
		<xsl:variable name="calcResult">
			<xsl:choose>
				<xsl:when test="./@type='stack'">
					<xsl:comment>calc type of STACK</xsl:comment>
					<xsl:value-of select="php:function( 'tmpl2Doc_calc' , ext:node-set($data)/stack )"/>
				</xsl:when>
			</xsl:choose>
		</xsl:variable>
		<xsl:choose>
			<xsl:when test="./@result-op='store'">
				<xsl:variable name="CalcName"><xsl:value-of select="ext:node-set($data)/result-name"/></xsl:variable>
				<xsl:choose>
					<xsl:when test="ext:node-set($data)/result-id">
						<xsl:variable name="CalcId"><xsl:value-of select="ext:node-set($data)/result-id"/></xsl:variable>
						<xsl:value-of select="php:function( 'tmpl2Doc_storeCalcResultWID' , $CalcName , $CalcId , $calcResult )"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="php:function( 'tmpl2Doc_storeCalcResultWOID' , $CalcName , $calcResult )"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			<xsl:otherwise>
				<xsl:copy-of select="$calcResult" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="//calc-result">
		<xsl:variable name="data">
			<xsl:apply-templates select="./*"/>
		</xsl:variable>
		<xsl:variable name="dataNode" select="ext:node-set($data)"/>

		<xsl:variable name="CalcName">
			<xsl:choose>
				<xsl:when test="$dataNode/result-name"><xsl:value-of select="$dataNode/result-name"/></xsl:when>
				<xsl:otherwise><xsl:value-of select="./@name"/></xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<xsl:variable name="CalcID">
			<xsl:choose>
				<xsl:when test="$dataNode/result-id"><xsl:value-of select="$dataNode/result-id"/></xsl:when>
				<xsl:when test="@id"><xsl:value-of select="@id"/></xsl:when>
				<xsl:otherwise></xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<xsl:variable name="resValue">
			<xsl:choose>
				<xsl:when test="$CalcID and not( $CalcID='' )"><xsl:value-of select="php:function( 'tmpl2Doc_restoreCalcResultWID' , $CalcName , $CalcID )"/></xsl:when>
				<xsl:otherwise><xsl:value-of select="php:function( 'tmpl2Doc_restoreCalcResultWOID' , $CalcName )"/></xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<xsl:variable name="resVirtVar">
			<xsl:element name="var"><xsl:value-of select="$resValue"/></xsl:element>
		</xsl:variable>

		<!--xsl:comment>Name: <xsl:value-of select="$CalcName"/></xsl:comment-->
		<xsl:comment>ID: <xsl:value-of select="$CalcID"/></xsl:comment>

		<xsl:call-template name="OutputVar">
			<xsl:with-param name="TagData" select="." />
			<xsl:with-param name="VarData" select="ext:node-set($resVirtVar)"/>
		</xsl:call-template>

	</xsl:template>

	<!--xsl:template match="//calc-result[@name]">
		<xsl:choose>
			<xsl:when test="./@id">
				<xsl:value-of select="php:function( 'tmpl2Doc_restoreCalcResultWID' , string( ./@name ) , string( ./@id ) )"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="php:function( 'tmpl2Doc_restoreCalcResultWOID' , string( ./@name ) )"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template-->

	<xsl:template match="//element-attr">
		<xsl:variable name="CEAN" select="@name"/>
		<xsl:comment><xsl:value-of select="$CEAN"/></xsl:comment>
		<xsl:value-of select="/NEWNODE/sub-var-row/@*[name(.)=$CEAN]"/>
	</xsl:template>

	<xsl:template match="//scan-list">
		<xsl:variable name="sln" select="./@name" />
		<xsl:value-of select="$sln"/>
		<xsl:for-each select="$docData/variables/scan-list[@name=$sln]/scan">
			<block>
				<xsl:element name="image">
					<xsl:attribute name="src"><xsl:value-of select="./@src"/></xsl:attribute>
					<xsl:attribute name="width">180mm</xsl:attribute>
					<xsl:attribute name="height">257mm</xsl:attribute>
				</xsl:element>
			</block>
		</xsl:for-each>
	</xsl:template>

	<xsl:template name="generateNodes">
		<xsl:param name="count" select="0"/>
		<xsl:param name="bound" select="1"/>
		<xsl:param name="accumulator"></xsl:param>
		<xsl:param name="item">node</xsl:param>

		<xsl:variable name="AS">
			<xsl:choose>
				<xsl:when test="$bound=1">
					<xsl:element name="{$item}"></xsl:element>
				</xsl:when>
				<xsl:otherwise>
					<xsl:copy-of select="$item"/><xsl:copy-of select="$item"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<xsl:variable name="NB" select="$bound*2"/>
		<xsl:variable name="TV" select="$count mod $NB"/>

		<xsl:variable name="res">
			<xsl:choose>
				<xsl:when test="$bound &lt;= $count">
					<xsl:choose>
						<xsl:when test="$TV &gt;= $bound">
							<xsl:call-template name="generateNodes">
								<xsl:with-param name="bound" select="$NB"/>
								<xsl:with-param name="count" select="$count"/>
								<xsl:with-param name="accumulator">
									<xsl:copy-of select="$accumulator"/><xsl:copy-of select="$AS"/>
								</xsl:with-param>
								<xsl:with-param name="item" select="$AS"/>
							</xsl:call-template>
						</xsl:when>
						<xsl:otherwise>
							<xsl:call-template name="generateNodes">
								<xsl:with-param name="bound" select="$NB"/>
								<xsl:with-param name="count" select="$count"/>
								<xsl:with-param name="accumulator">
									<xsl:copy-of select="$accumulator"/>
								</xsl:with-param>
								<xsl:with-param name="item" select="$AS"/>
							</xsl:call-template>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:when>
				<xsl:otherwise>
					<xsl:copy-of select="$accumulator"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:copy-of select="$res"/>
	</xsl:template>

	<xsl:template match="//dyna-con[@type='cycle']">
		<xsl:variable name="iterCount" select="./@count" />
		<xsl:variable name="tmpl" select="./element[1]" />
		<xsl:variable name="DynaConVarContent">
			<result>
				<xsl:call-template name="generateNodes">
					<xsl:with-param name="count" select="$iterCount"/>
				</xsl:call-template>
			</result>
		</xsl:variable>
		<!--xsl:copy-of select="$tmpl"/-->
		<xsl:for-each select="ext:node-set( $DynaConVarContent )/result/*">
			<xsl:apply-templates select="$tmpl/*"/>
		</xsl:for-each>
	</xsl:template>

	<xsl:template match="//image[@type='dmtx']">
		<xsl:element name="image">
			<xsl:attribute name="type"><xsl:value-of select="./@type"/></xsl:attribute>
			<xsl:attribute name="id"><xsl:value-of select="./@id"/></xsl:attribute>
			<xsl:attribute name="width"><xsl:value-of select="./@width"/></xsl:attribute>
			<xsl:attribute name="height"><xsl:value-of select="./@height"/></xsl:attribute>
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>

	<xsl:template name="attr--image-user-def">
		<xsl:attribute name="type"><xsl:value-of select="./@type"/></xsl:attribute>
		<xsl:if test="./@width">
			<xsl:attribute name="width"><xsl:value-of select="./@width"/></xsl:attribute>
		</xsl:if>
		<xsl:if test="./@height">
			<xsl:attribute name="height"><xsl:value-of select="./@height"/></xsl:attribute>
		</xsl:if>
		<xsl:if test="./@max-width">
			<xsl:attribute name="max-width"><xsl:value-of select="./@max-width"/></xsl:attribute>
		</xsl:if>
		<xsl:if test="./@max-height">
			<xsl:attribute name="max-height"><xsl:value-of select="./@max-height"/></xsl:attribute>
		</xsl:if>
	</xsl:template>

	<xsl:template match="//image[@type='user-def' and @name]">
		<xsl:comment>image with name <xsl:value-of select="@name"/></xsl:comment>
		<xsl:variable name="imageVar" select="$docData/variables/var[@name=current()/@name]"/>
		<xsl:element name="image">
			<xsl:attribute name="id"><xsl:value-of select="$imageVar/@id"/></xsl:attribute>
			<xsl:call-template name="attr--image-user-def"/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="//image[@type='user-def' and @path]">
		<xsl:comment>image with path <xsl:value-of select="@path"/></xsl:comment>
		<xsl:variable name="imageVar" select="$docData/variables//*[@path=current()/@path]"/>
		<xsl:element name="image">
			<xsl:attribute name="id"><xsl:value-of select="$imageVar/@id"/></xsl:attribute>
			<xsl:call-template name="attr--image-user-def"/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="//image[@type='user-def' and @sub-var-name]">
		<xsl:comment>image with sub-var-name <xsl:value-of select="@sub-var-name"/></xsl:comment>
		<xsl:variable name="CSVN" select="@sub-var-name"/>
		<xsl:variable name="IMG_ID">
			<xsl:choose>
				<xsl:when test="$CSVN='.'">
					<xsl:value-of select="/NEWNODE/sub-var-row-id"/>
				</xsl:when>
				<xsl:otherwise><xsl:value-of select="/NEWNODE/sub-var-list/*[name(.)=$CSVN]/@id"/></xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:element name="image">
			<xsl:attribute name="id"><xsl:value-of select="$IMG_ID"/></xsl:attribute>
			<xsl:call-template name="attr--image-user-def"/>
		</xsl:element>
	</xsl:template>



</xsl:transform>
