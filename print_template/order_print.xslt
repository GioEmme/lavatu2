<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
      xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
      xmlns:fo="http://www.w3.org/1999/XSL/Format">
  <xsl:output method="xml" indent="yes"/>
	<xsl:template match="/">
		<fo:root>
		  <fo:layout-master-set>
			<fo:simple-page-master master-name="A4-portrait"
				  page-height="29.7cm" page-width="21.0cm" margin="1cm">
			  <fo:region-body/>
			</fo:simple-page-master>
		  </fo:layout-master-set>
		  <fo:page-sequence master-reference="A4-portrait">
			<fo:flow flow-name="xsl-region-body">
				<fo:table  width="100%" height="13cm" border-style="solid" border-width="0.02cm">
					<fo:table-column column-width="16%"/>
					<fo:table-column column-width="16%"/>
					<fo:table-column column-width="16%"/>
					<fo:table-column column-width="16%"/>
					<fo:table-column column-width="16%"/>
					<fo:table-column column-width="20%"/>
					<fo:table-body>
						<fo:table-row>
							<fo:table-cell number-columns-spanned="6">
								<fo:block>
									<fo:external-graphic height="30pt" content-height="25pt" src="file:///c:/xampp/htdocs/lavatu2/img/brand_image.jpg" />
								</fo:block>
							</fo:table-cell>
						</fo:table-row>
						<fo:table-row>
							<fo:table-cell number-columns-spanned="3">
								<fo:block>
									Ordine n.: <fo:inline font-weight="bold"><xsl:value-of select="/order/order_id"/></fo:inline>
								</fo:block>
							</fo:table-cell>
							<fo:table-cell number-columns-spanned="3">
								<fo:block>
									Cliente: <fo:inline font-weight="bold"><xsl:value-of select="concat(concat(/order/nome, ' '), /order/cognome)"/></fo:inline>
								</fo:block>
							</fo:table-cell>
						</fo:table-row>
						<fo:table-row>
							<fo:table-cell number-columns-spanned="3">
								<fo:block>
									Data consegna: <fo:inline font-weight="bold"><xsl:value-of select="/order/data_consegna"/></fo:inline>
								</fo:block>
							</fo:table-cell>
							<fo:table-cell number-columns-spanned="3">
								<fo:block>
									Cellulare: <fo:inline font-weight="bold"><xsl:value-of select="/order/cellulare"/></fo:inline>
								</fo:block>
							</fo:table-cell>
						</fo:table-row>
						<fo:table-row>
							<fo:table-cell number-columns-spanned="6" height="6cm" border-bottom-style="solid" border-bottom-width="0.02cm">
								<fo:table width="100%">
									<fo:table-column column-width="50%"/>
									<fo:table-column column-width="50%"/>
									<fo:table-header>
										<fo:table-cell>
											<fo:block text-align="center" background-color="#cccccc">Articoli</fo:block>
										</fo:table-cell>
										<fo:table-cell>
											<fo:block text-align="center" background-color="#cccccc">Servizi</fo:block>
										</fo:table-cell>
									</fo:table-header>
									<fo:table-body>
										<fo:table-row>
											<fo:table-cell>
												<fo:table width="100%">
													<fo:table-column column-width="80%"/>
													<fo:table-column column-width="20%"/>
													<fo:table-header>
														<fo:table-cell>
															<fo:block text-align="center" background-color="#eeeeee">Descrizione</fo:block>
														</fo:table-cell>
														<fo:table-cell>
															<fo:block text-align="center" background-color="#eeeeee">Q.ta'</fo:block>
														</fo:table-cell>
													</fo:table-header>
													<fo:table-body>
														<fo:table-row><fo:table-cell><fo:block></fo:block></fo:table-cell></fo:table-row>
														<xsl:for-each select="/order/articoli/articolo">
															<fo:table-row>
																<fo:table-cell>
																	<fo:block>
																		<xsl:value-of select="articolo_descrizione"/>
																	</fo:block>
																</fo:table-cell>
																<fo:table-cell>
																	<fo:block text-align="right">
																		<xsl:value-of select="articolo_quantita"/>
																	</fo:block>
																</fo:table-cell>
															</fo:table-row>
														</xsl:for-each>
													</fo:table-body>
												</fo:table>
											</fo:table-cell>
											<fo:table-cell border-left-style="solid" border-left-width="0.02cm">
												<fo:table width="100%">
													<fo:table-column column-width="60%"/>
													<fo:table-column column-width="20%"/>
													<fo:table-column column-width="20%"/>
													<fo:table-header>
														<fo:table-cell>
															<fo:block text-align="center" background-color="#eeeeee">Descrizione</fo:block>
														</fo:table-cell>
														<fo:table-cell>
															<fo:block text-align="center" background-color="#eeeeee">€</fo:block>
														</fo:table-cell>
														<fo:table-cell>
															<fo:block text-align="center" background-color="#eeeeee">Q.ta'</fo:block>
														</fo:table-cell>
													</fo:table-header>
													<fo:table-body>
														<fo:table-row><fo:table-cell><fo:block></fo:block></fo:table-cell></fo:table-row>
														<xsl:for-each select="/order/servizi/servizio">
															<fo:table-row>
																<fo:table-cell>
																	<fo:block>
																		<xsl:value-of select="servizio_descrizione"/>
																	</fo:block>
																</fo:table-cell>
																<fo:table-cell>
																	<fo:block text-align="right">
																		<xsl:value-of select="servizio_prezzo"/>
																	</fo:block>
																</fo:table-cell>
																<fo:table-cell>
																	<fo:block text-align="right">
																		<xsl:value-of select="servizio_quantita"/>
																	</fo:block>
																</fo:table-cell>
															</fo:table-row>
														</xsl:for-each>
														<fo:table-row>
															<fo:table-cell number-columns-spanned="2">
																<fo:block background-color="#eeeeee">
																	Totale (€)
																</fo:block>
															</fo:table-cell>
															<fo:table-cell background-color="#eeeeee">
																<fo:block text-align="right">
																	<fo:inline font-weight="bold"><xsl:value-of select="/order/tot_lavorazione"/></fo:inline>
																</fo:block>
															</fo:table-cell>
														</fo:table-row>
													</fo:table-body>
												</fo:table>
											</fo:table-cell>
										</fo:table-row>
									</fo:table-body>
								</fo:table>
							</fo:table-cell>
						</fo:table-row>
						<fo:table-row height="2cm">
							<fo:table-cell number-columns-spanned="3" border-bottom-style="solid" border-bottom-width="0.02cm">
								<fo:block-container overflow="hidden" height="2cm">
									<fo:block>
										Note: <xsl:apply-templates select="/order/note_lavorazione"/>
									</fo:block>
								</fo:block-container>
							</fo:table-cell>
							<fo:table-cell number-columns-spanned="3" border-bottom-style="solid" border-bottom-width="0.02cm">
								<fo:block-container overflow="hidden" height="2cm">
									<fo:block>
										Presa visione: <xsl:apply-templates select="/order/presa_visione"/>
									</fo:block>
								</fo:block-container>
							</fo:table-cell>
						</fo:table-row>
						<fo:table-row>
							<fo:table-cell number-columns-spanned="6" text-align="center" border-bottom-style="solid" border-bottom-width="0.02cm">
								<fo:block>
									<fo:inline font-weight="bold" font-size="10">Il cliente autorizza il lavaggio del capo con macchina Revita contrariamente a quanto indicato dall etichetta sollevando la ditta da qualsiasi responsabilita riguardo eventuali danni che ne potrebbero derivare.</fo:inline>
								</fo:block>
							</fo:table-cell>
						</fo:table-row>
						<fo:table-row>
							<fo:table-cell number-columns-spanned="6" height="1cm" border-bottom-style="solid" border-bottom-width="0.02cm">
								<fo:block text-align="right" font-size="8">
									Firma
								</fo:block>
							</fo:table-cell>
						</fo:table-row>
						<fo:table-row>
							<fo:table-cell number-columns-spanned="6" text-align="center" border-bottom-style="solid" border-bottom-width="0.02cm">
								<fo:block>
									<fo:inline font-weight="bold" font-size="10">I capi possono essere ritirati ESCLUSIVAMENTE esibendo la presente ricevuta</fo:inline>
								</fo:block>
							</fo:table-cell>
						</fo:table-row>
						<fo:table-row>
							<fo:table-cell number-columns-spanned="6" text-align="center">
								<fo:block font-size="10">
									Copia per il cliente
								</fo:block>
							</fo:table-cell>
						</fo:table-row>
					</fo:table-body>
				</fo:table>
				<fo:table width="100%"><fo:table-column column-width="100%"/><fo:table-body><fo:table-row><fo:table-cell height="1cm"><fo:block></fo:block></fo:table-cell></fo:table-row></fo:table-body></fo:table>
				<fo:table  width="100%" height="13cm" border-style="solid" border-width="0.02cm">
					<fo:table-column column-width="16%"/>
					<fo:table-column column-width="16%"/>
					<fo:table-column column-width="16%"/>
					<fo:table-column column-width="16%"/>
					<fo:table-column column-width="16%"/>
					<fo:table-column column-width="20%"/>
					<fo:table-body>
						<fo:table-row>
							<fo:table-cell number-columns-spanned="6">
								<fo:block>
									<fo:external-graphic height="30pt" content-height="25pt" src="file:///c:/xampp/htdocs/lavatu2/img/brand_image.jpg" />
								</fo:block>
							</fo:table-cell>
						</fo:table-row>
						<fo:table-row>
							<fo:table-cell number-columns-spanned="3">
								<fo:block>
									Ordine n.: <fo:inline font-weight="bold"><xsl:value-of select="/order/order_id"/></fo:inline>
								</fo:block>
							</fo:table-cell>
							<fo:table-cell number-columns-spanned="3">
								<fo:block>
									Cliente: <fo:inline font-weight="bold"><xsl:value-of select="concat(concat(/order/nome, ' '), /order/cognome)"/></fo:inline>
								</fo:block>
							</fo:table-cell>
						</fo:table-row>
						<fo:table-row>
							<fo:table-cell number-columns-spanned="3">
								<fo:block>
									Data consegna: <fo:inline font-weight="bold"><xsl:value-of select="/order/data_consegna"/></fo:inline>
								</fo:block>
							</fo:table-cell>
							<fo:table-cell number-columns-spanned="3">
								<fo:block>
									Cellulare: <fo:inline font-weight="bold"><xsl:value-of select="/order/cellulare"/></fo:inline>
								</fo:block>
							</fo:table-cell>
						</fo:table-row>
						<fo:table-row>
							<fo:table-cell number-columns-spanned="6" height="6cm" border-bottom-style="solid" border-bottom-width="0.02cm">
								<fo:table width="100%">
									<fo:table-column column-width="50%"/>
									<fo:table-column column-width="50%"/>
									<fo:table-header>
										<fo:table-cell>
											<fo:block text-align="center" background-color="#cccccc">Articoli</fo:block>
										</fo:table-cell>
										<fo:table-cell>
											<fo:block text-align="center" background-color="#cccccc">Servizi</fo:block>
										</fo:table-cell>
									</fo:table-header>
									<fo:table-body>
										<fo:table-row>
											<fo:table-cell>
												<fo:table width="100%">
													<fo:table-column column-width="80%"/>
													<fo:table-column column-width="20%"/>
													<fo:table-header>
														<fo:table-cell>
															<fo:block text-align="center" background-color="#eeeeee">Descrizione</fo:block>
														</fo:table-cell>
														<fo:table-cell>
															<fo:block text-align="center" background-color="#eeeeee">Q.ta'</fo:block>
														</fo:table-cell>
													</fo:table-header>
													<fo:table-body>
														<fo:table-row><fo:table-cell><fo:block></fo:block></fo:table-cell></fo:table-row>
														<xsl:for-each select="/order/articoli/articolo">
															<fo:table-row>
																<fo:table-cell>
																	<fo:block>
																		<xsl:value-of select="articolo_descrizione"/>
																	</fo:block>
																</fo:table-cell>
																<fo:table-cell>
																	<fo:block text-align="right">
																		<xsl:value-of select="articolo_quantita"/>
																	</fo:block>
																</fo:table-cell>
															</fo:table-row>
														</xsl:for-each>
													</fo:table-body>
												</fo:table>
											</fo:table-cell>
											<fo:table-cell border-left-style="solid" border-left-width="0.02cm">
												<fo:table width="100%">
													<fo:table-column column-width="60%"/>
													<fo:table-column column-width="20%"/>
													<fo:table-column column-width="20%"/>
													<fo:table-header>
														<fo:table-cell>
															<fo:block text-align="center" background-color="#eeeeee">Descrizione</fo:block>
														</fo:table-cell>
														<fo:table-cell>
															<fo:block text-align="center" background-color="#eeeeee">€</fo:block>
														</fo:table-cell>
														<fo:table-cell>
															<fo:block text-align="center" background-color="#eeeeee">Q.ta'</fo:block>
														</fo:table-cell>
													</fo:table-header>
													<fo:table-body>
														<fo:table-row><fo:table-cell><fo:block></fo:block></fo:table-cell></fo:table-row>
														<xsl:for-each select="/order/servizi/servizio">
															<fo:table-row>
																<fo:table-cell>
																	<fo:block>
																		<xsl:value-of select="servizio_descrizione"/>
																	</fo:block>
																</fo:table-cell>
																<fo:table-cell>
																	<fo:block text-align="right">
																		<xsl:value-of select="servizio_prezzo"/>
																	</fo:block>
																</fo:table-cell>
																<fo:table-cell>
																	<fo:block text-align="right">
																		<xsl:value-of select="servizio_quantita"/>
																	</fo:block>
																</fo:table-cell>
															</fo:table-row>
														</xsl:for-each>
														<fo:table-row>
															<fo:table-cell number-columns-spanned="2">
																<fo:block background-color="#eeeeee">
																	Totale (€)
																</fo:block>
															</fo:table-cell>
															<fo:table-cell background-color="#eeeeee">
																<fo:block text-align="right">
																	<fo:inline font-weight="bold"><xsl:value-of select="/order/tot_lavorazione"/></fo:inline>
																</fo:block>
															</fo:table-cell>
														</fo:table-row>
													</fo:table-body>
												</fo:table>
											</fo:table-cell>
										</fo:table-row>
									</fo:table-body>
								</fo:table>
							</fo:table-cell>
						</fo:table-row>
						<fo:table-row height="2cm">
							<fo:table-cell number-columns-spanned="3" border-bottom-style="solid" border-bottom-width="0.02cm">
								<fo:block-container overflow="hidden" height="2cm">
									<fo:block>
										Note: <xsl:apply-templates select="/order/note_lavorazione"/>
									</fo:block>
								</fo:block-container>
							</fo:table-cell>
							<fo:table-cell number-columns-spanned="3" border-bottom-style="solid" border-bottom-width="0.02cm">
								<fo:block-container overflow="hidden" height="2cm">
									<fo:block>
										Presa visione: <xsl:apply-templates select="/order/presa_visione"/>
									</fo:block>
								</fo:block-container>
							</fo:table-cell>
						</fo:table-row>
						<fo:table-row>
							<fo:table-cell number-columns-spanned="6" text-align="center" border-bottom-style="solid" border-bottom-width="0.02cm">
								<fo:block>
									<fo:inline font-weight="bold" font-size="10">Il cliente autorizza il lavaggio del capo con macchina Revita contrariamente a quanto indicato dall etichetta sollevando la ditta da qualsiasi responsabilita riguardo eventuali danni che ne potrebbero derivare.</fo:inline>
								</fo:block>
							</fo:table-cell>
						</fo:table-row>
						<fo:table-row>
							<fo:table-cell number-columns-spanned="6" height="1cm" border-bottom-style="solid" border-bottom-width="0.02cm">
								<fo:block text-align="right" font-size="8">
									Firma
								</fo:block>
							</fo:table-cell>
						</fo:table-row>
						<fo:table-row>
							<fo:table-cell number-columns-spanned="6" text-align="center" border-bottom-style="solid" border-bottom-width="0.02cm">
								<fo:block>
									<fo:inline font-weight="bold" font-size="10">I capi possono essere ritirati ESCLUSIVAMENTE esibendo la presente ricevuta</fo:inline>
								</fo:block>
							</fo:table-cell>
						</fo:table-row>
						<fo:table-row>
							<fo:table-cell number-columns-spanned="6" text-align="center">
								<fo:block font-size="10">
									Copia per l'esercente
								</fo:block>
							</fo:table-cell>
						</fo:table-row>
					</fo:table-body>
				</fo:table>
			</fo:flow>
		  </fo:page-sequence>
		</fo:root>
	</xsl:template>
	
	<xsl:template match="strong">
        <fo:inline font-weight="bold">
            <xsl:apply-templates select="node()"/>
        </fo:inline>  
    </xsl:template>
	
	<xsl:template match="em">
		<fo:inline font-style="italic"><xsl:apply-templates select="node()"/></fo:inline>
    </xsl:template>

	<xsl:template match="span[contains(@style, 'underline')]">
		<fo:inline text-decoration="underline"><xsl:apply-templates select="node()"/></fo:inline>
    </xsl:template>

	<xsl:template match="span[contains(@style, 'color')]">
		<fo:inline><xsl:attribute name="color"><xsl:value-of select="substring(substring-after(./@style, 'color: '),1,7)" /></xsl:attribute><xsl:apply-templates select="node()"/></fo:inline>
    </xsl:template>

	<xsl:template match="br">
		<fo:block/>
    </xsl:template>
</xsl:stylesheet>