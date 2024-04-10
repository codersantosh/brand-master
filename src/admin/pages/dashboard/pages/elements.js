/* WordPress */
import { __ } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';

/* Library */
import classNames from 'classnames';
import { cloneDeep, map } from 'lodash';

/*Atrc*/
import {
	AtrcText,
	AtrcWireFrameContentSidebar,
	AtrcWireFrameHeaderContentFooter,
	AtrcPrefix,
	AtrcPanelBody,
	AtrcPanelRow,
	AtrcContent,
	AtrcTitleTemplate1,
	AtrcControlToggle,
	AtrcRepeaterGroupAdd,
	AtrcControlSelect,
	AtrcControlSelectPost,
	AtrcControlText,
	AtrcNestedObjUpdateByKey2,
	AtrcControlIconPicker,
	AtrcControlIconPickerDefaultIcons,
	AtrcControlCheckbox,
	AtrcControlSelectButton,
	AtrcNestedObjDeleteByKey3,
	AtrcNestedObjUpdateByKey4,
	AtrcNestedObjAddByKey2,
	AtrcLabel,
	AtrcControlSortable,
	AtrcRepeater,
	AtrcRepeaterGroup,
	AtrcControlLink,
} from 'atrc';

/* Inbuilt */
import { AtrcReduxContextData } from '../../../routes';
import { DocsTitle } from '../../../components/molecules';

/*Local*/
const MainContent = () => {
	const data = useContext(AtrcReduxContextData);

	const { dbSettings, dbUpdateSetting } = data;

	const { dashboard = {} } = dbSettings;

	const {
		siteIdentity = {},
		userInfo = {},
		logout = {},
		menu = {},
		social = {},
	} = dashboard;

	return (
		<AtrcContent className={classNames(AtrcPrefix('cont-dashboard'))}>
			<AtrcLabel>{__('Elements settings', 'brand-master')}</AtrcLabel>
			<AtrcPanelBody
				variant='st'
				title={__('Site identity', 'brand-master')}
				initialOpen={true}>
				<AtrcPanelRow className={classNames('at-m')}>
					<AtrcControlSelectButton
						label={__('Logo display options', 'brand-master')}
						value={siteIdentity.logo}
						options={[
							{
								label: __('Hide', 'brand-master'),
								value: '',
							},
							{
								label: __('Top', 'brand-master'),
								value: 't',
							},
							{
								label: __('Right', 'brand-master'),
								value: 'r',
							},
							{
								label: __('Bottom', 'brand-master'),
								value: 'b',
							},
							{
								label: __('Left', 'brand-master'),
								value: 'l',
							},
						]}
						onChange={(newVal) => {
							const updatedSettings = AtrcNestedObjUpdateByKey2({
								settings: dashboard,
								key1: 'siteIdentity',
								key2: 'logo',
								val2: newVal,
							});
							dbUpdateSetting('dashboard', updatedSettings);
						}}
					/>
				</AtrcPanelRow>
				<AtrcPanelRow className={classNames('at-flx-col', 'at-al-itm-st')}>
					<AtrcControlSortable
						label={__('Site title and tagline', 'brand-master')}
						onChange={(newVal) => {
							const updatedSettings = AtrcNestedObjUpdateByKey2({
								settings: dashboard,
								key1: 'siteIdentity',
								key2: 'sort',
								val2: newVal,
							});
							dbUpdateSetting('dashboard', updatedSettings);
						}}
						value={siteIdentity.sort}
						items={[
							{
								value: 'title',
								children: (
									<AtrcControlCheckbox
										label={__('Site title', 'brand-master')}
										checked={siteIdentity.title}
										onChange={(newVal) => {
											const updatedSettings = AtrcNestedObjUpdateByKey2({
												settings: dashboard,
												key1: 'siteIdentity',
												key2: 'title',
												val2: newVal,
											});
											dbUpdateSetting('dashboard', updatedSettings);
										}}
									/>
								),
							},
							{
								value: 'tagline',
								children: (
									<AtrcControlCheckbox
										label={__('Site tagline', 'brand-master')}
										checked={siteIdentity.tagline}
										onChange={(newVal) => {
											const updatedSettings = AtrcNestedObjUpdateByKey2({
												settings: dashboard,
												key1: 'siteIdentity',
												key2: 'tagline',
												val2: newVal,
											});
											dbUpdateSetting('dashboard', updatedSettings);
										}}
									/>
								),
							},
						]}
					/>
				</AtrcPanelRow>
			</AtrcPanelBody>

			<AtrcPanelBody
				title={__('Login user info', 'brand-master')}
				initialOpen={false}>
				<AtrcPanelRow className={classNames('at-m')}>
					<AtrcControlSelectButton
						label={__('Avatar display options', 'brand-master')}
						value={userInfo.logo}
						options={[
							{
								label: __('Hide', 'brand-master'),
								value: '',
							},
							{
								label: __('Top', 'brand-master'),
								value: 't',
							},
							{
								label: __('Right', 'brand-master'),
								value: 'r',
							},
							{
								label: __('Bottom', 'brand-master'),
								value: 'b',
							},
							{
								label: __('Left', 'brand-master'),
								value: 'l',
							},
						]}
						onChange={(newVal) => {
							const updatedSettings = AtrcNestedObjUpdateByKey2({
								settings: dashboard,
								key1: 'userInfo',
								key2: 'logo',
								val2: newVal,
							});
							dbUpdateSetting('dashboard', updatedSettings);
						}}
					/>
				</AtrcPanelRow>
				<AtrcPanelRow className={classNames('at-flx-col', 'at-al-itm-st')}>
					<AtrcControlSortable
						label={__('User name and description', 'brand-master')}
						onChange={(newVal) => {
							const updatedSettings = AtrcNestedObjUpdateByKey2({
								settings: dashboard,
								key1: 'userInfo',
								key2: 'sort',
								val2: newVal,
							});
							dbUpdateSetting('dashboard', updatedSettings);
						}}
						value={userInfo.sort}
						items={[
							{
								value: 'name',
								children: (
									<AtrcControlCheckbox
										label={__('User name', 'brand-master')}
										checked={userInfo.name}
										onChange={(newVal) => {
											const updatedSettings = AtrcNestedObjUpdateByKey2({
												settings: dashboard,
												key1: 'userInfo',
												key2: 'name',
												val2: newVal,
											});
											dbUpdateSetting('dashboard', updatedSettings);
										}}
									/>
								),
							},
							{
								value: 'desc',
								children: (
									<AtrcControlCheckbox
										label={__('User description', 'brand-master')}
										checked={userInfo.desc}
										onChange={(newVal) => {
											const updatedSettings = AtrcNestedObjUpdateByKey2({
												settings: dashboard,
												key1: 'userInfo',
												key2: 'desc',
												val2: newVal,
											});
											dbUpdateSetting('dashboard', updatedSettings);
										}}
									/>
								),
							},
						]}
					/>
				</AtrcPanelRow>
			</AtrcPanelBody>

			<AtrcPanelBody
				title={__('Logout', 'brand-master')}
				initialOpen={false}>
				<AtrcPanelRow className={classNames('at-m')}>
					<AtrcControlText
						label={__('Logout label', 'brand-master')}
						value={logout.label}
						onChange={(newVal) => {
							const updatedSettings = AtrcNestedObjUpdateByKey2({
								settings: dashboard,
								key1: 'logout',
								key2: 'label',
								val2: newVal,
							});
							dbUpdateSetting('dashboard', updatedSettings);
						}}
					/>
				</AtrcPanelRow>
				<AtrcPanelRow className={classNames('at-m')}>
					<AtrcControlIconPicker
						className={classNames('at-flx-grw-1')}
						label={__('Select icon', 'brand-master')}
						options={AtrcControlIconPickerDefaultIcons()}
						value={logout.icon}
						modalProps={{
							title: __('Icons', 'brand-master'),
						}}
						onChange={(newVal) => {
							const updatedSettings = AtrcNestedObjUpdateByKey2({
								settings: dashboard,
								key1: 'logout',
								key2: 'icon',
								val2: newVal,
							});
							dbUpdateSetting('dashboard', updatedSettings);
						}}
						closeOnIconSelect={true}
					/>
				</AtrcPanelRow>
			</AtrcPanelBody>

			<AtrcPanelBody
				title={__('Menus & pages', 'brand-master')}
				initialOpen={false}>
				<AtrcPanelRow className={classNames('at-m')}>
					<AtrcControlText
						label={__('Menu heading', 'brand-master')}
						value={menu.heading}
						onChange={(newVal) => {
							const updatedSettings = AtrcNestedObjUpdateByKey2({
								settings: dashboard,
								key1: 'menu',
								key2: 'heading',
								val2: newVal,
							});
							dbUpdateSetting('dashboard', updatedSettings);
						}}
					/>
				</AtrcPanelRow>
				<AtrcPanelRow className={classNames('at-m')}>
					<AtrcRepeater
						label={__('Add menus & pages', 'brand-master')}
						isSortable={true}
						value={menu.items}
						onChange={(newVal) => {
							const updatedSettings = AtrcNestedObjUpdateByKey2({
								settings: dashboard,
								key1: 'menu',
								key2: 'items',
								val2: newVal,
							});
							dbUpdateSetting('dashboard', updatedSettings);
						}}
						groups={() =>
							map(menu.items, (item, itemIndex) => (
								<AtrcRepeaterGroup
									key={itemIndex}
									groupIndex={itemIndex}
									deleteGroup={(itmIndex) => {
										const updatedSettings = AtrcNestedObjDeleteByKey3({
											settings: dashboard,
											key1: 'menu',
											key2: 'items',
											key3: itemIndex,
										});
										dbUpdateSetting('dashboard', updatedSettings);
									}}
									groupTitle={sprintf(
										// translators: %s: placeholder for idx
										__('Item %d', 'brand-master'),
										itemIndex + 1
									)}
									deleteTitle={__('Remove item', 'brand-master')}>
									<AtrcPanelRow className={classNames('at-m')}>
										<AtrcControlText
											label={__('Menu label', 'brand-master')}
											value={item.label}
											onChange={(newVal) => {
												const updatedSettings = AtrcNestedObjUpdateByKey4({
													settings: dashboard,
													key1: 'menu',
													key2: 'items',
													key3: itemIndex,
													key4: 'label',
													val4: newVal,
												});
												dbUpdateSetting('dashboard', updatedSettings);
											}}
										/>
									</AtrcPanelRow>
									<AtrcPanelRow className={classNames('at-m')}>
										<AtrcControlText
											label={__('Menu slug', 'brand-master')}
											value={item.slug}
											onChange={(newVal) => {
												const updatedSettings = AtrcNestedObjUpdateByKey4({
													settings: dashboard,
													key1: 'menu',
													key2: 'items',
													key3: itemIndex,
													key4: 'slug',
													val4: newVal,
												});
												dbUpdateSetting('dashboard', updatedSettings);
											}}
										/>
									</AtrcPanelRow>
									<AtrcPanelRow className={classNames('at-m')}>
										<AtrcControlSelectPost
											label={__('Select page', 'brand-master')}
											wrapProps={{
												className: 'at-flx-grw-1',
											}}
											value={item.typeId}
											onChange={(newVal) => {
												const updatedSettings = AtrcNestedObjUpdateByKey4({
													settings: dashboard,
													key1: 'menu',
													key2: 'items',
													key3: itemIndex,
													key4: 'typeId',
													val4: newVal,
												});
												dbUpdateSetting('dashboard', updatedSettings);
											}}
											postType='page'
										/>
									</AtrcPanelRow>
									<AtrcPanelRow className={classNames('at-m')}>
										<AtrcControlIconPicker
											className={classNames('at-flx-grw-1')}
											label={__('Select icon', 'brand-master')}
											options={AtrcControlIconPickerDefaultIcons()}
											value={item.icon}
											modalProps={{
												title: __('Icons', 'brand-master'),
											}}
											onChange={(newVal) => {
												const updatedSettings = AtrcNestedObjUpdateByKey4({
													settings: dashboard,
													key1: 'menu',
													key2: 'items',
													key3: itemIndex,
													key4: 'icon',
													val4: newVal,
												});
												dbUpdateSetting('dashboard', updatedSettings);
											}}
											closeOnIconSelect={true}
										/>
									</AtrcPanelRow>
								</AtrcRepeaterGroup>
							))
						}
						addGroup={() => (
							<AtrcRepeaterGroupAdd
								addGroup={() => {
									const addedSettings = AtrcNestedObjAddByKey2({
										settings: dashboard,
										key1: 'menu',
										key2: 'items',
										val2: {
											label: '',
											slug: '',
											typeId: 0,
											icon: {},
										},
									});
									dbUpdateSetting('dashboard', addedSettings);
								}}
								tooltipText={__('Add item', 'brand-master')}
								label={__('Add item', 'brand-master')}
							/>
						)}
					/>
				</AtrcPanelRow>
				<AtrcPanelRow className={classNames('at-m')}>
					<AtrcControlToggle
						label={__('Add logout on menu', 'brand-master')}
						checked={menu.logout}
						onChange={() => {
							const updatedSettings = AtrcNestedObjUpdateByKey2({
								settings: dashboard,
								key1: 'menu',
								key2: 'logout',
								val2: !menu.logout,
							});
							dbUpdateSetting('dashboard', updatedSettings);
						}}
					/>
				</AtrcPanelRow>
				<AtrcPanelRow className={classNames('at-m')}>
					<AtrcControlLink
						allowOn={true}
						allowTitle={false}
						allowTarget={false}
						label={__(
							'Redirect non-logged-in visitor from menu pages',
							'brand-master'
						)}
						value={menu.redirect}
						onChange={(newVal) => {
							const updatedSettings = AtrcNestedObjUpdateByKey2({
								settings: dashboard,
								key1: 'menu',
								key2: 'redirect',
								val2: newVal,
							});
							dbUpdateSetting('dashboard', updatedSettings);
						}}
						onProps={{
							label: __('Enable redirection', 'brand-master'),
						}}
						urlProps={{
							label: __('Redirect url', 'brand-master'),
						}}
					/>
				</AtrcPanelRow>
			</AtrcPanelBody>

			<AtrcPanelBody
				title={__('Social links', 'brand-master')}
				initialOpen={false}>
				<AtrcPanelRow className={classNames('at-m')}>
					<AtrcControlText
						label={__('Social heading', 'brand-master')}
						value={social.heading}
						onChange={(newVal) => {
							const updatedSettings = AtrcNestedObjUpdateByKey2({
								settings: dashboard,
								key1: 'social',
								key2: 'heading',
								val2: newVal,
							});
							dbUpdateSetting('dashboard', updatedSettings);
						}}
					/>
				</AtrcPanelRow>
				<AtrcPanelRow className={classNames('at-m')}>
					<AtrcRepeater
						label={__('Add social menus', 'brand-master')}
						isSortable={true}
						groups={() =>
							map(social.items, (item, itemIndex) => (
								<AtrcRepeaterGroup
									key={itemIndex}
									groupIndex={itemIndex}
									deleteGroup={(itmIndex) => {
										const updatedSettings = AtrcNestedObjDeleteByKey3({
											settings: dashboard,
											key1: 'social',
											key2: 'items',
											key3: itemIndex,
										});
										dbUpdateSetting('dashboard', updatedSettings);
									}}
									groupTitle={sprintf(
										// translators: %s: placeholder for idx
										__('Item %d', 'brand-master'),
										itemIndex + 1
									)}
									deleteTitle={__('Remove item', 'brand-master')}>
									<AtrcPanelRow className={classNames('at-m')}>
										<AtrcControlText
											label={__('Menu label', 'brand-master')}
											value={item.label}
											onChange={(newVal) => {
												const updatedSettings = AtrcNestedObjUpdateByKey4({
													settings: dashboard,
													key1: 'social',
													key2: 'items',
													key3: itemIndex,
													key4: 'label',
													val4: newVal,
												});
												dbUpdateSetting('dashboard', updatedSettings);
											}}
										/>
									</AtrcPanelRow>
									<AtrcPanelRow className={classNames('at-m')}>
										<AtrcControlText
											label={__('Menu link', 'brand-master')}
											value={item.url}
											type='url'
											onChange={(newVal) => {
												const updatedSettings = AtrcNestedObjUpdateByKey4({
													settings: dashboard,
													key1: 'social',
													key2: 'items',
													key3: itemIndex,
													key4: 'url',
													val4: newVal,
												});
												dbUpdateSetting('dashboard', updatedSettings);
											}}
										/>
									</AtrcPanelRow>
									<AtrcPanelRow className={classNames('at-m')}>
										<AtrcControlToggle
											label={__('Open in new tab', 'brand-master')}
											checked={item.target === '_blank'}
											onChange={() => {
												let newVal = '';
												if (item.target !== '_blank') {
													newVal = '_blank';
												}
												const updatedSettings = AtrcNestedObjUpdateByKey4({
													settings: dashboard,
													key1: 'social',
													key2: 'items',
													key3: itemIndex,
													key4: 'target',
													val4: newVal,
												});
												dbUpdateSetting('dashboard', updatedSettings);
											}}
										/>
									</AtrcPanelRow>

									<AtrcPanelRow className={classNames('at-m')}>
										<AtrcControlIconPicker
											className={classNames('at-flx-grw-1')}
											label={__('Select icon', 'brand-master')}
											options={AtrcControlIconPickerDefaultIcons()}
											value={item.icon}
											modalProps={{
												title: __('Icons', 'brand-master'),
											}}
											onChange={(newVal) => {
												const updatedSettings = AtrcNestedObjUpdateByKey4({
													settings: dashboard,
													key1: 'social',
													key2: 'items',
													key3: itemIndex,
													key4: 'icon',
													val4: newVal,
												});
												dbUpdateSetting('dashboard', updatedSettings);
											}}
											closeOnIconSelect={true}
										/>
									</AtrcPanelRow>
								</AtrcRepeaterGroup>
							))
						}
						addGroup={() => (
							<AtrcRepeaterGroupAdd
								addGroup={() => {
									const addedSettings = AtrcNestedObjAddByKey2({
										settings: dashboard,
										key1: 'social',
										key2: 'items',
										val2: {
											label: '',
											slug: '',
											url: '',
											icon: {},
										},
									});
									dbUpdateSetting('dashboard', addedSettings);
								}}
								tooltipText={__('Add item', 'brand-master')}
								label={__('Add item', 'brand-master')}
							/>
						)}
					/>
				</AtrcPanelRow>
				<AtrcPanelRow className={classNames('at-m')}>
					<AtrcControlSelect
						label={__('Select', 'brand-master')}
						value={social.layout}
						options={[
							{
								label: __('Vertical', 'brand-master'),
								value: '',
							},
							{
								label: __('Horizontal', 'brand-master'),
								value: 'hor',
							},
						]}
						onChange={(newVal) => {
							const updatedSettings = AtrcNestedObjUpdateByKey2({
								settings: dashboard,
								key1: 'social',
								key2: 'layout',
								val2: newVal,
							});
							dbUpdateSetting('dashboard', updatedSettings);
						}}
					/>
				</AtrcPanelRow>
			</AtrcPanelBody>
		</AtrcContent>
	);
};

const Documentation = () => {
	const data = useContext(AtrcReduxContextData);

	const { lsSettings, lsSaveSettings } = data;

	return (
		<AtrcWireFrameHeaderContentFooter
			headerRowProps={{
				className: classNames(AtrcPrefix('header-docs'), 'at-m'),
			}}
			renderHeader={
				<DocsTitle
					onClick={() => {
						const localStorageClone = cloneDeep(lsSettings);
						localStorageClone.bmdElDocs1 = !localStorageClone.bmdElDocs1;
						lsSaveSettings(localStorageClone);
					}}
				/>
			}
			renderContent={
				<>
					<AtrcPanelBody
						className={classNames(AtrcPrefix('m-0'))}
						title={__('What are Dashboard Elements?', 'brand-master')}
						initialOpen={true}>
						<AtrcText
							tag='p'
							className={classNames(AtrcPrefix('m-0'), 'at-m')}>
							{__(
								'Dashboard Elements refer to the customizable components that can be used in the dashboard sidebar and header. These elements allow you to personalize and tailor your dashboard according to your preferences. The content displayed in the dashboard is sourced from the page content, providing a dynamic and user-friendly experience.',
								'brand-master'
							)}
						</AtrcText>
					</AtrcPanelBody>
					<AtrcPanelBody
						title={__('How to customize Elements?', 'brand-master')}
						initialOpen={false}>
						<AtrcText
							tag='p'
							className={classNames(AtrcPrefix('m-0'), 'at-m')}>
							{__(
								'Customizing elements is a breeze! Simply navigate to the options available for each element. To showcase your customized elements, head over to the header and sidebar content settings. There, you can effortlessly enable, disable, or rearrange these elements, ensuring a tailored and seamless display that suits your preferences.',
								'brand-master'
							)}
						</AtrcText>
					</AtrcPanelBody>
				</>
			}
			allowHeaderRow={false}
			allowHeaderCol={false}
			allowContentRow={false}
			allowContentCol={false}
		/>
	);
};

const Settings = () => {
	const data = useContext(AtrcReduxContextData);
	const { lsSettings } = data;

	const { bmdElDocs1 } = lsSettings;

	return (
		<AtrcWireFrameHeaderContentFooter
			wrapProps={{
				className: classNames(AtrcPrefix('bg-white'), 'at-bg-cl'),
			}}
			renderHeader={
				<AtrcTitleTemplate1
					title={__('Frontend Dashboard settings', 'brand-master')}
				/>
			}
			renderContent={
				<AtrcWireFrameContentSidebar
					wrapProps={{
						allowContainer: true,
						type: 'fluid',
						tag: 'section',
						className: 'at-p',
					}}
					renderContent={<MainContent />}
					renderSidebar={!bmdElDocs1 ? <Documentation /> : null}
					contentProps={{
						contentCol: bmdElDocs1 ? 'at-col-12' : 'at-col-7',
					}}
					sidebarProps={{
						sidebarCol: 'at-col-5',
					}}
				/>
			}
			allowHeaderRow={false}
			allowHeaderCol={false}
			allowContentRow={false}
			allowContentCol={false}
		/>
	);
};

export default Settings;
