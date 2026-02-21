<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9">
    <xsl:output method="html" encoding="UTF-8" indent="yes"/>
    
    <xsl:template match="/">
        <html>
            <head>
                <title>XML Sitemap</title>
                <meta charset="UTF-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1"/>
                <style>
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }
                    
                    body {
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        min-height: 100vh;
                        padding: 40px 20px;
                    }
                    
                    .container {
                        max-width: 1200px;
                        margin: 0 auto;
                        background: white;
                        border-radius: 10px;
                        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                        overflow: hidden;
                    }
                    
                    .header {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        padding: 40px 30px;
                        text-align: center;
                    }
                    
                    .header h1 {
                        font-size: 2em;
                        margin-bottom: 10px;
                    }
                    
                    .header p {
                        opacity: 0.9;
                        font-size: 1.1em;
                    }
                    
                    .content {
                        padding: 30px;
                    }
                    
                    .info-box {
                        background: #f0f4ff;
                        border-left: 4px solid #667eea;
                        padding: 15px 20px;
                        margin-bottom: 30px;
                        border-radius: 5px;
                    }
                    
                    .info-box strong {
                        color: #667eea;
                    }
                    
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 20px 0;
                    }
                    
                    thead {
                        background: #f8f9fa;
                    }
                    
                    th {
                        padding: 15px;
                        text-align: left;
                        font-weight: 600;
                        color: #333;
                        border-bottom: 2px solid #dee2e6;
                    }
                    
                    td {
                        padding: 12px 15px;
                        border-bottom: 1px solid #dee2e6;
                    }
                    
                    tr:hover {
                        background: #f8f9fa;
                    }
                    
                    a {
                        color: #667eea;
                        text-decoration: none;
                        word-break: break-all;
                    }
                    
                    a:hover {
                        text-decoration: underline;
                    }
                    
                    .date {
                        color: #6c757d;
                        font-size: 0.9em;
                    }
                    
                    .changefreq {
                        display: inline-block;
                        background: #e7f3ff;
                        color: #0066cc;
                        padding: 3px 8px;
                        border-radius: 3px;
                        font-size: 0.85em;
                    }
                    
                    .priority {
                        display: inline-block;
                        background: #fff3cd;
                        color: #856404;
                        padding: 3px 8px;
                        border-radius: 3px;
                        font-size: 0.85em;
                    }
                    
                    .stats {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                        gap: 20px;
                        margin: 30px 0;
                    }
                    
                    .stat-card {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        padding: 20px;
                        border-radius: 8px;
                        text-align: center;
                    }
                    
                    .stat-card .number {
                        font-size: 2.5em;
                        font-weight: bold;
                        display: block;
                    }
                    
                    .stat-card .label {
                        font-size: 0.9em;
                        opacity: 0.9;
                    }
                    
                    .footer {
                        background: #f8f9fa;
                        padding: 20px 30px;
                        text-align: center;
                        color: #6c757d;
                        font-size: 0.9em;
                        border-top: 1px solid #dee2e6;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>XML Sitemap</h1>
                        <p>This XML Sitemap helps search engines discover and index your content</p>
                    </div>
                    
                    <div class="content">
                        <xsl:choose>
                            <xsl:when test="sitemap:sitemapindex">
                                <xsl:call-template name="sitemapindex"/>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:call-template name="urlset"/>
                            </xsl:otherwise>
                        </xsl:choose>
                    </div>
                </div>
            </body>
        </html>
    </xsl:template>
    
    <xsl:template name="sitemapindex">
        <div class="info-box">
            <strong>Sitemap Index</strong><br/>
            This is a sitemap index that contains links to individual sitemaps for different content types.
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <span class="number"><xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)"/></span>
                <span class="label">Sitemaps</span>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Sitemap URL</th>
                    <th>Last Modified</th>
                </tr>
            </thead>
            <tbody>
                <xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
                    <tr>
                        <td>
                            <a href="{sitemap:loc}">
                                <xsl:value-of select="sitemap:loc"/>
                            </a>
                        </td>
                        <td class="date">
                            <xsl:if test="sitemap:lastmod">
                                <xsl:value-of select="sitemap:lastmod"/>
                            </xsl:if>
                        </td>
                    </tr>
                </xsl:for-each>
            </tbody>
        </table>
    </xsl:template>
    
    <xsl:template name="urlset">
        <div class="info-box">
            <strong>Sitemap</strong><br/>
            This sitemap contains URLs for your website content, helping search engines discover and index pages.
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <span class="number"><xsl:value-of select="count(sitemap:urlset/sitemap:url)"/></span>
                <span class="label">URLs</span>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>URL</th>
                    <th>Last Modified</th>
                    <th>Change Frequency</th>
                    <th>Priority</th>
                </tr>
            </thead>
            <tbody>
                <xsl:for-each select="sitemap:urlset/sitemap:url">
                    <tr>
                        <td>
                            <a href="{sitemap:loc}">
                                <xsl:value-of select="sitemap:loc"/>
                            </a>
                        </td>
                        <td class="date">
                            <xsl:if test="sitemap:lastmod">
                                <xsl:value-of select="sitemap:lastmod"/>
                            </xsl:if>
                        </td>
                        <td>
                            <xsl:if test="sitemap:changefreq">
                                <span class="changefreq">
                                    <xsl:value-of select="sitemap:changefreq"/>
                                </span>
                            </xsl:if>
                        </td>
                        <td>
                            <xsl:if test="sitemap:priority">
                                <span class="priority">
                                    <xsl:value-of select="sitemap:priority"/>
                                </span>
                            </xsl:if>
                        </td>
                    </tr>
                </xsl:for-each>
            </tbody>
        </table>
    </xsl:template>
</xsl:stylesheet>
